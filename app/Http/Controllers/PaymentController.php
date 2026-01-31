<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Level;
use App\Models\Grade;
use App\Models\Section;
use App\Models\PaymentConcept;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /** ================= LISTADO DE PAGOS ================= */
    public function index(Request $request)
    {
        $levels   = Level::all();
        $grades   = Grade::all();
        $sections = Section::all();

        // Consulta optimizada con joins
        $query = Payment::select(
                'payments.*',
                'students.nombres',
                'students.apellido_paterno',
                'students.apellido_materno',
                'students.dni',
                'levels.nombre as level_nombre',
                'grades.nombre as grade_nombre',
                'sections.nombre as section_nombre'
            )
            ->join('enrollments', 'payments.enrollment_id', '=', 'enrollments.id')
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->join('levels', 'enrollments.level_id', '=', 'levels.id')
            ->join('grades', 'enrollments.grade_id', '=', 'grades.id')
            ->join('sections', 'enrollments.section_id', '=', 'sections.id');

        // Aplicar filtros
        if ($request->level_id) {
            $query->where('enrollments.level_id', $request->level_id);
        }

        if ($request->grade_id) {
            $query->where('enrollments.grade_id', $request->grade_id);
        }

        if ($request->section_id) {
            $query->where('enrollments.section_id', $request->section_id);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('students.dni', 'like', "%$search%")
                ->orWhere('students.nombres', 'like', "%$search%")
                ->orWhere('students.apellido_paterno', 'like', "%$search%")
                ->orWhere('students.apellido_materno', 'like', "%$search%");
            });
        }

        // Ordenar y paginar
        $payments = $query->orderBy('payments.updated_at', 'desc')
            ->paginate(5)
            ->appends($request->query());

        return view('payments.index', compact('payments', 'levels', 'grades', 'sections'));
    }
    /** ================= FORMULARIO CREAR ================= */
    public function create()
    {
        $conceptos = PaymentConcept::where('activo', true)->get();

        return view('payments.create', compact('conceptos'));
    }


    /** ================= BUSCAR MATRÍCULA POR DNI (AJAX) ================= */
    public function buscarPagosPorDni($dni)
    {
        $student = Student::where('dni', $dni)->first();

        if (!$student) {
            return response()->json([], 200);
        }

        $payments = Payment::with([
                'paymentConcept', // 🔴 CLAVE
                'enrollment.level',
                'enrollment.grade',
                'enrollment.section'
            ])
            ->whereHas('enrollment', function ($q) use ($student) {
                $q->where('student_id', $student->id);
            })
            ->orderBy('fecha_pago', 'desc')
            ->take(50)
            ->get();

        return response()->json($payments, 200);
    }

    /** ================= BUSCAR MATRÍCULA POR DNI (para registrar pago) ================= */
    public function buscarPorDni($dni)
    {
        $student = Student::where('dni', $dni)->first();

        if (!$student) {
            return response()->json(['error' => 'No se encontró el estudiante.']);
        }

        $enrollment = Enrollment::with(['level', 'grade', 'section'])
            ->where('student_id', $student->id)
            ->latest()
            ->first();

        if (!$enrollment) {
            return response()->json(['error' => 'El estudiante no tiene matrícula registrada.']);
        }

        return response()->json([
            'enrollment_id' => $enrollment->id,
            'estudiante' => "{$student->nombres} {$student->apellido_paterno} {$student->apellido_materno}",
            'nivel' => $enrollment->level->nombre,
            'grado' => $enrollment->grade->nombre,
            'seccion' => $enrollment->section->nombre,
        ]);
    }

    /** ================= GUARDAR PAGO + GENERAR PDF ================= */  //ahi me qudee
    public function store(Request $request)
    {
        $request->validate([
            'enrollment_id' => 'required|exists:enrollments,id',
            'conceptos'     => 'required|array|min:1',
            'metodo_pago'   => 'required|in:efectivo,yape,plin,transferencia',
        ]);

        DB::beginTransaction();

        try {
            $enrollment = Enrollment::findOrFail($request->enrollment_id);
            $payments = collect();

            foreach ($request->conceptos as $conceptId => $data) {
                if (!isset($data['activo'])) continue; // Solo conceptos seleccionados

                $concept   = PaymentConcept::findOrFail($conceptId);
                $monto     = $data['monto'] ?? 0;
                $descuento = min($data['descuento'] ?? 0, $monto);

                // 🔹 Si es mensual, crear un pago por cada mes seleccionado
                if ($concept->es_mensual) {
                    $periodos = $request->input('periodos', []);

                    if (empty($periodos)) {
                        throw new \Exception("Debe seleccionar al menos un mes para el concepto {$concept->nombre}");
                    }

                    foreach ($periodos as $periodo) {
                        $existe = Payment::where('enrollment_id', $enrollment->id)
                            ->where('payment_concept_id', $concept->id)
                            ->where('periodo', $periodo)
                            ->exists();

                        if ($existe) {
                            throw new \Exception("El mes {$periodo} del concepto {$concept->nombre} ya está pagado.");
                        }

                        $payments->push(
                            Payment::create([
                                'enrollment_id'      => $enrollment->id,
                                'payment_concept_id' => $concept->id,
                                'periodo'            => $periodo,
                                'monto'              => $monto,
                                'descuento'          => $descuento,
                                'fecha_pago'         => now(),
                                'metodo_pago'        => $request->metodo_pago,
                                'estado'             => 'pagado',
                            ])
                        );
                    }
                } 
                // 🔹 Si NO es mensual, solo un registro
                else {
                    $existe = Payment::where('enrollment_id', $enrollment->id)
                        ->where('payment_concept_id', $concept->id)
                        ->whereNull('periodo')
                        ->exists();

                    if ($existe) {
                        throw new \Exception("El concepto {$concept->nombre} ya fue pagado.");
                    }

                    $payments->push(
                        Payment::create([
                            'enrollment_id'      => $enrollment->id,
                            'payment_concept_id' => $concept->id,
                            'periodo'            => null,
                            'monto'              => $monto,
                            'descuento'          => $descuento,
                            'fecha_pago'         => now(),
                            'metodo_pago'        => $request->metodo_pago,
                            'estado'             => 'pagado',
                        ])
                    );
                }
            }

            if ($payments->isEmpty()) {
                throw new \Exception("No se seleccionó ningún concepto válido para pagar.");
            }

            // ✅ Generar PDF con todos los pagos nuevos
            $payments = Payment::with([
                'paymentConcept',
                'enrollment.student',
                'enrollment.level',
                'enrollment.grade',
                'enrollment.section',
            ])->whereIn('id', $payments->pluck('id'))
            ->orderBy('payment_concept_id')
            ->get();

            $pdf = Pdf::loadView('payments.pdf', [
                'payments' => $payments
            ])->setPaper('A4');

            $voucher = 'vouchers/pago_' . time() . '.pdf';
            Storage::disk('public')->put($voucher, $pdf->output());

            // 🔹 Asignar voucher a todos los pagos creados
            Payment::whereIn('id', $payments->pluck('id'))
                ->update(['voucher' => $voucher]);

            DB::commit();

            return redirect()
                ->route('payments.index')
                ->with('success', 'Pagos registrados correctamente.')
                ->with('voucher_pdf', route('voucher.view', basename($voucher)));

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }


  /** ================= VER PDF ================= */
    public function pdf($enrollmentId)
    {
        // Obtener pagos con relaciones necesarias
        $payments = Payment::with([
            'paymentConcept',
            'enrollment.student',
            'enrollment.level',
            'enrollment.grade',
            'enrollment.section'
        ])
        ->where('enrollment_id', $enrollmentId)
        ->orderBy('periodo')
        ->get();

        // Verificar si hay pagos
        if ($payments->isEmpty()) {
            // Crear un objeto vacío con datos del estudiante (si quieres)
            $student = Enrollment::with('student')->find($enrollmentId)?->student;
            return Pdf::loadView('payments.pdf', compact('payments', 'student'))
                    ->setPaper('A4')
                    ->setOption('defaultFont', 'DejaVu Sans')
                    ->stream('comprobante_pagos.pdf');
        }

        // Guardar el PDF en storage (opcional)
        $filename = 'pago_' . time() . '.pdf';
        $path = storage_path('app/public/vouchers/' . $filename);

        $pdf = Pdf::loadView('payments.pdf', compact('payments'))
                ->setPaper('A4')
                ->setOption('defaultFont', 'DejaVu Sans');

        $pdf->save($path);

        // Actualizar ruta en la base de datos del primer pago (opcional)
        Payment::where('enrollment_id', $enrollmentId)
            ->update(['voucher' => 'vouchers/' . $filename]);

        return $pdf->stream($filename);
    }
    public function verVoucher($filename)
    {
        $path = storage_path('app/public/vouchers/' . $filename);

        if (!file_exists($path)) {
            abort(404, 'El voucher no existe o fue eliminado.');
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /** ================= EDITAR ================= */
    public function edit(Payment $payment)
    {
        $payment->load('enrollment.student', 'paymentConcept');
        $conceptos = PaymentConcept::where('activo', true)->get();

        return view('payments.edit', [
            'payment' => $payment,
            'conceptos' => $conceptos,
            'periodo_actual' => $payment->periodo,
        ]);
    }


    /** ================= ACTUALIZAR ================= */
    /** ================= ACTUALIZAR ================= */
    public function update(Request $request, Payment $payment)
    {
        $request->validate([
            'payment_concept_id' => 'required|exists:payment_concepts,id',
            'periodo'            => 'nullable|string|max:7',
            'monto'              => 'required|numeric|min:0',
            'descuento'          => 'nullable|numeric|min:0',
            'metodo_pago'        => 'required|in:efectivo,yape,plin,transferencia',
            'estado'             => 'required|in:pendiente,pagado,validado',
        ]);

        DB::beginTransaction();

        try {
            // 🔹 Obtener concepto y calcular descuento
            $concept   = PaymentConcept::findOrFail($request->payment_concept_id);
            $descuento = min($request->descuento ?? 0, $request->monto);

            // 🔹 Actualizar solo este pago
            $payment->update([
                'payment_concept_id' => $concept->id,
                'periodo'            => $request->periodo,
                'monto'              => $request->monto,
                'descuento'          => $descuento,
                'metodo_pago'        => $request->metodo_pago,
                'estado'             => $request->estado,
                'fecha_pago'         => now(),
            ]);

            // 🔹 Cargar relaciones necesarias para el PDF
            $payment->load([
                'paymentConcept',
                'enrollment.student',
                'enrollment.level',
                'enrollment.grade',
                'enrollment.section',
            ]);

            // 🔹 Generar PDF solo con este pago
            $pdf = Pdf::loadView('payments.pdf', [
                'payments' => collect([$payment]) // 👈 Solo este pago
            ])->setPaper('A4');

            // 🔹 Guardar el nuevo comprobante PDF
            $voucher = 'vouchers/pago_' . $payment->id . '_' . time() . '.pdf';
            Storage::disk('public')->put($voucher, $pdf->output());

            // 🔹 Actualizar la ruta del voucher en este pago
            $payment->update(['voucher' => $voucher]);

            DB::commit();

            return redirect()
                ->route('payments.index')
                ->with('success', 'Pago actualizado correctamente y PDF regenerado.')
                ->with('voucher_pdf', route('voucher.view', basename($voucher)));

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

}