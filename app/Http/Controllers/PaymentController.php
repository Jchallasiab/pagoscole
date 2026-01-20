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
        $payments = $query->orderBy('payments.fecha_pago', 'desc')
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
            'payment_concept_id' => 'required|exists:payment_concepts,id',
            'periodo' => 'nullable|string|max:7',
            'monto' => 'required|numeric|min:0',
            'descuento' => 'nullable|numeric|min:0',
            'metodo_pago' => 'required|in:efectivo,yape,plin,transferencia',
        ]);

        DB::beginTransaction();

        try {
            // 🔹 Matrícula
            $enrollment = Enrollment::findOrFail($request->enrollment_id);

            // 🔹 Concepto
            $concept = PaymentConcept::findOrFail($request->payment_concept_id);

            // 🔹 Validar duplicado (concepto + periodo)
            $existePago = Payment::where('enrollment_id', $enrollment->id)
                ->where('payment_concept_id', $concept->id)
                ->where('periodo', $request->periodo)
                ->exists();

            if ($existePago) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'periodo' => 'Ya existe un pago para este periodo y concepto.'
                    ]);
            }


            // 🔹 Descuento seguro
            $descuento = min($request->descuento ?? 0, $request->monto);

            // ✅ GUARDAR PAGO
            $payment = Payment::create([
                'enrollment_id'       => $enrollment->id,
                'payment_concept_id'  => $concept->id,
                'periodo'             => $request->periodo,
                'monto'               => $request->monto,
                'descuento'           => $descuento,
                'fecha_pago'          => now(),
                'metodo_pago'         => $request->metodo_pago,
                'estado'              => 'pagado',
            ]);

            // 🔹 Cargar relaciones para el PDF
            $payment->load([
                'enrollment.student',
                'enrollment.level',
                'enrollment.grade',
                'enrollment.section',
                'paymentConcept',
            ]);

            // 📄 GENERAR PDF
            $pdf = Pdf::loadView('payments.pdf', compact('payment'));

            // 🔹 Guardar voucher
            $ruta = "vouchers/voucher_pago_{$payment->id}.pdf";
            Storage::disk('public')->put($ruta, $pdf->output());

            // 🔹 Guardar ruta del voucher
            $payment->update([
                'voucher' => $ruta,
            ]);

            DB::commit();

            // ✅ ENVIAR DIRECTO AL PDF
            DB::commit();
            return redirect()->route('payments.pdf', $payment->id);

        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

  /** ================= VER PDF ================= */
    public function pdf(Payment $payment)
    {
        $payment->load('enrollment.student', 'paymentConcept');

        // 🔹 Recalcular total y descuento (por seguridad)
        $payment->monto = $payment->monto - $payment->descuento;

        $pdf = Pdf::loadView('payments.pdf', compact('payment'))
            ->setPaper('A4')
            ->setOption('defaultFont', 'DejaVu Sans');
        return $pdf->stream("voucher_pago_{$payment->id}.pdf");
    }

    /** ================= EDITAR ================= */
    public function edit(Payment $payment)
    {
        $payment->load('enrollment.student', 'paymentConcept');
        $conceptos = PaymentConcept::where('activo', true)->get();

        return view('payments.edit', compact('payment', 'conceptos'));
    }

    /** ================= ACTUALIZAR ================= */
    public function update(Request $request, Payment $payment)
    {
        $request->validate([
            'payment_concept_id' => 'required|exists:payment_concepts,id',
            'periodo' => 'nullable|string|max:7',
            'monto' => 'required|numeric|min:0',
            'descuento' => 'nullable|numeric|min:0',
            'metodo_pago' => 'required|in:efectivo,yape,plin,transferencia',
            'estado' => 'required|in:pendiente,pagado,validado',
        ]);

        $concept = PaymentConcept::findOrFail($request->payment_concept_id);
        $descuento = min($request->descuento ?? 0, $request->monto);

        $payment->update([
            'payment_concept_id' => $concept->id,
            'periodo' => $request->periodo,
            'monto' => $request->monto,
            'descuento' => $descuento,
            'metodo_pago' => $request->metodo_pago,
            'estado' => $request->estado,
        ]);

        // 🔥 Regenerar PDF
        $payment->load('enrollment.student', 'paymentConcept');
        $pdf = PDF::loadView('payments.pdf', compact('payment'));
        $ruta = "vouchers/voucher_pago_{$payment->id}.pdf";
        Storage::disk('public')->put($ruta, $pdf->output());
        $payment->update(['voucher' => $ruta]);

        return redirect()->route('payments.index')
            ->with('success', 'Pago actualizado correctamente y PDF regenerado.');
    }

    /** ================= ELIMINAR ================= */
    public function destroy(Payment $payment)
    {
        if ($payment->voucher && Storage::disk('public')->exists($payment->voucher)) {
            Storage::disk('public')->delete($payment->voucher);
        }

        $payment->delete();

        return redirect()->route('payments.index')
            ->with('success', 'Pago eliminado correctamente.');
    }
}