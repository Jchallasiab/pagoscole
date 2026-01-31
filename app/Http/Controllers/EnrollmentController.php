<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Payment;
use App\Models\Level;
use App\Models\Grade;
use App\Models\Section;
use App\Models\SchoolYear;
use App\Models\PaymentConcept;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Milon\Barcode\DNS2D;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;


require_once app_path('Libraries/fpdf.php');

class EnrollmentController extends Controller
{
    /* =========================
    LISTADO + FILTROS
    ========================== */
    public function index(Request $request)
    {
        $levels   = Level::where('activo', true)->get();
        $grades   = Grade::where('activo', true)->get();
        $sections = Section::where('activo', true)->get();
        $years    = SchoolYear::where('activo', true)->get();

        // Consulta optimizada con joins
        $query = Enrollment::query()
            ->select(
                'enrollments.*',
                'students.nombres',
                'students.apellido_paterno',
                'students.apellido_materno',
                'students.dni',
                'levels.nombre as level_nombre',
                'grades.nombre as grade_nombre',
                'sections.nombre as section_nombre',
                'school_years.nombre as year_nombre'
            )
            ->join('students', 'enrollments.student_id', '=', 'students.id')
            ->join('levels', 'enrollments.level_id', '=', 'levels.id')
            ->join('grades', 'enrollments.grade_id', '=', 'grades.id')
            ->join('sections', 'enrollments.section_id', '=', 'sections.id')
            ->join('school_years', 'enrollments.school_year_id', '=', 'school_years.id');

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
        if ($request->school_year_id) {
            $query->where('enrollments.school_year_id', $request->school_year_id);
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

        $enrollments = $query->orderBy('enrollments.fecha_matricula', 'desc')
            ->paginate(5)
            ->appends($request->query());

        return view('enrollments.index', compact(
            'enrollments','levels','grades','sections','years'
        ));
    }
    public function getSections(Request $request)
    {
        $request->validate([
            'school_year_id' => 'required|exists:school_years,id',
            'grade_id'       => 'required|exists:grades,id',
        ]);

        return Section::where('school_year_id', $request->school_year_id)
            ->where('grade_id', $request->grade_id)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }
    /* =========================
       CREAR MATRÍCULA
    ========================== */
    public function create()
    {
        $students = Student::where('estado', 'activo')
            ->select('id', 'nombres', 'apellido_paterno', 'apellido_materno', 'dni')
            ->get();

        $levels = Level::select('id', 'nombre')->get();
        $grades = Grade::select('id', 'nombre', 'level_id')->get();
        $schoolYears = SchoolYear::select('id', 'nombre')->get();

        // 👌 Secciones NO se cargan aquí
        $sections = collect();

        // 🧩 NUEVO: obtener los conceptos de pago activos
        $paymentConcepts = PaymentConcept::where('activo', true)->get();

        return view(
            'enrollments.create',
            compact(
                'students',
                'levels',
                'grades',
                'sections',
                'schoolYears',
                'paymentConcepts' // 👈 importante
            )
        );
    }

    /* =========================
       GUARDAR MATRÍCULA
    ========================== */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            // =========================
            // VALIDACIÓN
            // =========================
            $request->validate([
                'student_id'     => 'required|exists:students,id',
                'school_year_id' => 'required|exists:school_years,id',
                'level_id'       => 'required|exists:levels,id',

                'grade_id' => [
                    'required',
                    Rule::exists('grades', 'id')
                        ->where('level_id', $request->level_id),
                ],

                'section_id' => [
                    'required',
                    Rule::exists('sections', 'id')
                        ->where('grade_id', $request->grade_id)
                        ->where('school_year_id', $request->school_year_id)
                        ->where('activo', true),
                ],

                'fecha_matricula' => 'required|date',
                'concepts'        => 'nullable|array',
            ]);

            // =========================
            // EVITAR MATRÍCULA DUPLICADA
            // =========================
            $exists = Enrollment::where('student_id', $request->student_id)
                ->where('school_year_id', $request->school_year_id)
                ->exists();

            if ($exists) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'student_id' => 'El estudiante ya está matriculado en este año.'
                    ]);
            }

            // =========================
            // CREAR MATRÍCULA
            // =========================
            $enrollment = Enrollment::create([
                'student_id'      => $request->student_id,
                'school_year_id'  => $request->school_year_id,
                'level_id'        => $request->level_id,
                'grade_id'        => $request->grade_id,
                'section_id'      => $request->section_id,
                'fecha_matricula' => $request->fecha_matricula,
                'estado'          => 'pagado',
            ]);

            // =========================
            // PAGOS
            // =========================
            if (is_array($request->concepts)) {

                foreach ($request->concepts as $conceptId => $data) {

                    if (!isset($data['selected'])) {
                        continue;
                    }

                    $concept = PaymentConcept::find($conceptId);
                    if (!$concept) continue;

                    $monto     = (float) ($data['monto'] ?? 0);
                    $descuento = (float) ($data['descuento'] ?? 0);
                    $metodo    = isset($data['metodo_pago'])
                        ? strtolower($data['metodo_pago'])
                        : null;

                    // 🔵 MENSUALIDADES
                    if ($concept->es_mensual) {

                        if (empty($data['periodos']) || !is_array($data['periodos'])) {
                            continue;
                        }

                        foreach ($data['periodos'] as $periodo) {

                            Payment::create([
                                'enrollment_id'      => $enrollment->id,
                                'payment_concept_id' => $conceptId,
                                'periodo'            => $periodo, // YYYY-MM
                                'monto'              => $monto,
                                'descuento'          => $descuento,
                                'fecha_pago'         => now(),
                                'metodo_pago'        => $metodo,
                                'estado'             => 'pagado',
                            ]);
                        }
                    }
                    // 🟡 CONCEPTO NORMAL
                    else {

                        Payment::create([
                            'enrollment_id'      => $enrollment->id,
                            'payment_concept_id' => $conceptId,
                            'periodo'            => null,
                            'monto'              => $monto,
                            'descuento'          => $descuento,
                            'fecha_pago'         => now(),
                            'metodo_pago'        => $metodo,
                            'estado'             => 'pagado',
                        ]);
                    }
                }
            }

            DB::commit();
            $this->generarVoucherFPDF($enrollment);

            return redirect()
            ->route('enrollments.index')
            ->with([
                'success' => 'Matrícula registrada correctamente.',
                'voucher_id' => $enrollment->id,
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return back()
                ->withErrors([
                    'error' => $e->getMessage()
                ]);
        }
    }
    /* =========================
       PDF + QR
    ========================== */
    private function generarVoucherFPDF(Enrollment $enrollment)
    {
        $student = $enrollment->student;
        $payments = $enrollment->payments()->with('paymentConcept')->get();

        /* ======================
        GENERAR QR
        ====================== */
        $qrPath = storage_path('app/public/qr_' . $student->dni . '.png');
        $dns = new DNS2D();
        $dns->setStorPath(storage_path('framework/cache/'));
        file_put_contents(
            $qrPath,
            base64_decode($dns->getBarcodePNG($student->dni, 'QRCODE'))
        );

        /* ======================
        CREAR PDF
        ====================== */
        $pdf = new \FPDF();
        $pdf->AddPage();

        /* ======================
        LOGO
        ====================== */
        $logoPath = public_path('img/logotesla.jpg');
        if (file_exists($logoPath)) {
            $pdf->Image($logoPath, 15, 12, 25);
        }

        /* ======================
        ENCABEZADO
        ====================== */
        $pdf->SetXY(45, 15);
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 8, utf8_decode('INSTITUCIÓN EDUCATIVA PRIVADA'), 0, 1);

        $pdf->SetFont('Arial', 'B', 15);
        $pdf->SetX(45);
        $pdf->Cell(0, 8, utf8_decode('TESLA BLACK HORSE'), 0, 1);

        $pdf->SetFont('Arial', '', 12);
        $pdf->SetX(45);
        $pdf->Cell(0, 8, utf8_decode('COMPROBANTE DE MATRÍCULA ' . $enrollment->schoolYear->anio), 0, 1);

        /* ======================
        DATOS DEL ESTUDIANTE
        ====================== */
        $pdf->Ln(18);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, utf8_decode('DATOS DEL ESTUDIANTE'), 0, 1);

        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(60, 8, 'Nombre:', 1);
        $pdf->Cell(0, 8, utf8_decode($student->nombres.' '.$student->apellido_paterno.' '.$student->apellido_materno), 1, 1);

        $pdf->Cell(60, 8, 'DNI:', 1);
        $pdf->Cell(0, 8, $student->dni, 1, 1);

        $pdf->Cell(60, 8, utf8_decode('Nivel / Grado / Sección:'), 1);
        $pdf->Cell(0, 8, utf8_decode(
            $enrollment->level->nombre.' - '.$enrollment->grade->nombre.' - '.$enrollment->section->nombre
        ), 1, 1);

        $pdf->Cell(60, 8, utf8_decode('Fecha matrícula:'), 1);
        $pdf->Cell(0, 8, $enrollment->fecha_matricula, 1, 1);

        /* ======================
        TABLA DE CONCEPTOS PAGADOS
        ====================== */
        $pdf->Ln(10);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, utf8_decode('DETALLE DE PAGOS'), 0, 1);

        // Encabezados
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(45, 8, 'Concepto', 1);
        $pdf->Cell(25, 8, 'Periodo', 1);
        $pdf->Cell(25, 8, 'Monto', 1);
        $pdf->Cell(25, 8, 'Desc.', 1);
        $pdf->Cell(25, 8, 'Total', 1);
        $pdf->Cell(30, 8, 'Metodo', 1, 1);

        $pdf->SetFont('Arial', '', 10);
        $totalGeneral = 0;

        foreach ($payments as $p) {
            $total = $p->monto - $p->descuento;
            $totalGeneral += $total;

            $pdf->Cell(45, 8, utf8_decode($p->paymentConcept->nombre), 1);
            $pdf->Cell(25, 8, utf8_decode($p->periodo ?? '—'), 1);
            $pdf->Cell(25, 8, 'S/ '.number_format($p->monto, 2), 1);
            $pdf->Cell(25, 8, 'S/ '.number_format($p->descuento, 2), 1);
            $pdf->Cell(25, 8, 'S/ '.number_format($total, 2), 1);
            $pdf->Cell(30, 8, ucfirst($p->metodo_pago), 1, 1);
        }

        // TOTAL GENERAL
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(120, 8, 'TOTAL PAGADO', 1);
        $pdf->Cell(55, 8, 'S/ '.number_format($totalGeneral, 2), 1, 1);

        /* ======================
        FOTO + QR
        ====================== */
        $y = $pdf->GetY() + 10;

        $fotoPath = $student->photo_path
            ? storage_path('app/public/'.$student->photo_path)
            : public_path('img/default-user.jpg');

        if (file_exists($fotoPath)) {
            $pdf->Image($fotoPath, 25, $y, 35, 45);
        }

        if (file_exists($qrPath)) {
            $pdf->Image($qrPath, 140, $y, 35, 35);
        }

        /* ======================
        GUARDAR PDF
        ====================== */
        $pdfPath = 'vouchers/voucher_matricula_' . $enrollment->id . '.pdf';
        Storage::disk('public')->put($pdfPath, $pdf->Output('S'));

        $enrollment->update([
            'voucher_matricula' => $pdfPath
        ]);
    }
    /* =========================
       VER PDF
    ========================== */
    public function voucher($id)
    {
        $enrollment = Enrollment::findOrFail($id);
        return response()->file(storage_path('app/public/' . $enrollment->voucher_matricula));
    }
    /** =========================
     * MOSTRAR DETALLE MATRÍCULA
     * ========================= */
    public function show($id)
    {
        $enrollment = Enrollment::with(['student','level','grade','section','schoolYear'])
            ->findOrFail($id);

        return view('enrollments.show', compact('enrollment'));
    }

    /* =========================
       EDITAR MATRÍCULA
    ========================== */
    public function edit($id)
    {
        $enrollment = Enrollment::with([
            'student',
            'level',
            'grade',
            'section',
            'schoolYear',
            'payments',
            'payments.paymentConcept'
        ])->findOrFail($id);

        $students = Student::where('estado', 'activo')
            ->select('id', 'nombres', 'apellido_paterno', 'apellido_materno', 'dni')
            ->get();

        $levels = Level::select('id', 'nombre')->get();

        // 👌 Todos los grados (igual que create)
        $grades = Grade::select('id', 'nombre', 'level_id')->get();

        // 👌 Secciones según matrícula actual
        $sections = Section::where('school_year_id', $enrollment->school_year_id)
            ->where('grade_id', $enrollment->grade_id)
            ->where('activo', true)
            ->get();

        $schoolYears = SchoolYear::select('id', 'nombre')->get();

        // 🔑 ESTA LÍNEA ES LA QUE FALTABA
        $paymentConcepts = PaymentConcept::where('activo', true)->get();

        return view(
            'enrollments.edit',
            compact(
                'enrollment',
                'students',
                'levels',
                'grades',
                'sections',
                'schoolYears',
                'paymentConcepts' // 👈 IMPORTANTE
            )
        );
    }
    /* =========================
       ACTUALIZAR MATRÍCULA
    ========================== */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {

            // =========================
            // VALIDACIÓN
            // =========================
            $request->validate([
                'student_id'     => 'required|exists:students,id',
                'school_year_id' => 'required|exists:school_years,id',
                'level_id'       => 'required|exists:levels,id',
                'grade_id'       => 'required|exists:grades,id',
                'section_id'     => 'required|exists:sections,id',
                'fecha_matricula'=> 'required|date',
                'concepts'       => 'nullable|array',
            ]);

            // =========================
            // MATRÍCULA
            // =========================
            $enrollment = Enrollment::findOrFail($id);

            $enrollment->update([
                'student_id'      => $request->student_id,
                'school_year_id'  => $request->school_year_id,
                'level_id'        => $request->level_id,
                'grade_id'        => $request->grade_id,
                'section_id'      => $request->section_id,
                'fecha_matricula' => $request->fecha_matricula,
                // ⚠️ estado NO se toca
            ]);

            // =========================
            // ELIMINAR PAGOS ANTERIORES
            // =========================
            Payment::where('enrollment_id', $enrollment->id)->delete();

            // =========================
            // REGISTRAR PAGOS NUEVOS
            // =========================
            if (is_array($request->concepts)) {

                foreach ($request->concepts as $conceptId => $data) {

                    if (!isset($data['selected'])) continue;

                    $concept = PaymentConcept::find($conceptId);
                    if (!$concept) continue;

                    $monto     = (float) ($data['monto'] ?? 0);
                    $descuento = (float) ($data['descuento'] ?? 0);
                    $metodo    = strtolower($data['metodo_pago'] ?? '');

                    // =========================
                    // 🔵 MENSUALIDADES
                    // =========================
                    if ($concept->es_mensual) {

                        if (empty($data['periodos'])) continue;

                        foreach ($data['periodos'] as $periodo) {

                            // 🔒 NORMALIZAR YYYY-MM
                            if (!str_contains($periodo, '-')) {
                                $periodo = date('Y') . '-' . $periodo;
                            }

                            Payment::create([
                                'enrollment_id'      => $enrollment->id,
                                'payment_concept_id' => $conceptId,
                                'periodo'            => $periodo, // ✅ 2026-03
                                'monto'              => $monto,
                                'descuento'          => $descuento,
                                'fecha_pago'         => now(),
                                'metodo_pago'        => $metodo,
                                'estado'             => 'pagado',
                            ]);
                        }

                    }
                    // =========================
                    // 🟡 CONCEPTO NORMAL
                    // =========================
                    else {

                        Payment::create([
                            'enrollment_id'      => $enrollment->id,
                            'payment_concept_id' => $conceptId,
                            'periodo'            => null,
                            'monto'              => $monto,
                            'descuento'          => $descuento,
                            'fecha_pago'         => now(),
                            'metodo_pago'        => $metodo,
                            'estado'             => 'pagado',
                        ]);
                    }
                }
            }

            DB::commit();

            // =========================
            // REGENERAR PDF
            // =========================
            $this->generarVoucherFPDF($enrollment);

            return redirect()
                ->route('enrollments.index')
                ->with('success', 'Matrícula actualizada correctamente.');

        } catch (\Throwable $e) {

            DB::rollBack();

            return back()->withErrors([
                'error' => $e->getMessage()
            ]);
        }
    }

    /* =========================
       ELIMINAR MATRÍCULA
    ========================== */
    public function destroy(Enrollment $enrollment)
    {
        // 🧹 Eliminar voucher si existe
        if ($enrollment->voucher_matricula && Storage::disk('public')->exists($enrollment->voucher_matricula)) {
            Storage::disk('public')->delete($enrollment->voucher_matricula);
        }

        $enrollment->delete();

        return redirect()
            ->route('enrollments.index')
            ->with('success', 'Matrícula eliminada correctamente');
    }

   /* =========================
       EXPORTAR EXCEL (CON FILTROS)
    ========================== */
    public function exportExcel(Request $request)
    {
        $fileName = 'matriculas_filtradas.xlsx';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados
        $sheet->fromArray([
            'DNI',
            'Estudiante',
            'Nivel',
            'Grado',
            'Sección',
            'Año Escolar',
            'Fecha Matrícula',
            'Estado'
        ], null, 'A1');

        $row = 2;

        // Chunk: procesamos 500 registros a la vez
        Enrollment::with(['student','level','grade','section','schoolYear'])
            ->when($request->school_year_id, fn($q) => $q->where('school_year_id', $request->school_year_id))
            ->when($request->level_id, fn($q) => $q->where('level_id', $request->level_id))
            ->when($request->grade_id, fn($q) => $q->where('grade_id', $request->grade_id))
            ->when($request->section_id, fn($q) => $q->where('section_id', $request->section_id))
            ->when($request->search, function ($q) use ($request) {
                $q->whereHas('student', function ($s) use ($request) {
                    $s->where('dni', 'like', "%{$request->search}%")
                    ->orWhere('nombres', 'like', "%{$request->search}%")
                    ->orWhere('apellido_paterno', 'like', "%{$request->search}%")
                    ->orWhere('apellido_materno', 'like', "%{$request->search}%");
                });
            })
            ->orderBy('fecha_matricula', 'desc')
            ->chunk(500, function($enrollments) use (&$sheet, &$row) {
                foreach ($enrollments as $enrollment) {
                    $sheet->fromArray([
                        $enrollment->student->dni,
                        $enrollment->student->nombres . ' ' . $enrollment->student->apellido_paterno . ' ' . $enrollment->student->apellido_materno,
                        $enrollment->level->nombre ?? '-',
                        $enrollment->grade->nombre ?? '-',
                        $enrollment->section->nombre ?? '-',
                        $enrollment->schoolYear->nombre ?? '-',
                        $enrollment->fecha_matricula,
                        ucfirst($enrollment->estado)
                    ], null, "A{$row}");
                    $row++;
                }
            });

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(
            fn() => $writer->save('php://output'),
            $fileName,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }
}