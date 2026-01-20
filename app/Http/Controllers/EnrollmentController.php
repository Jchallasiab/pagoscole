<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Payment;
use App\Models\Level;
use App\Models\Grade;
use App\Models\Section;
use App\Models\SchoolYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Milon\Barcode\DNS2D;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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

        return view(
            'enrollments.create',
            compact(
                'students',
                'levels',
                'grades',
                'sections',
                'schoolYears'
            )
        );
    }

    /* =========================
       GUARDAR MATRÍCULA
    ========================== */
    public function store(Request $request)
    {
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
            'monto_matricula' => 'required|numeric|min:0',
        ]);

        // =========================
        // EVITAR MATRÍCULA DUPLICADA
        // =========================
        $existing = Enrollment::where('student_id', $request->student_id)
            ->where('school_year_id', $request->school_year_id)
            ->where('estado', 'pagado')
            ->first();

        if ($existing) {
            return back()
                ->withInput()
                ->withErrors([
                    'student_id' => 'El estudiante ya está matriculado en este año escolar.'
                ]);
        }

        // =========================
        // CREAR MATRÍCULA (PRIMERO)
        // =========================
        $enrollment = Enrollment::create([
            'student_id'      => $request->student_id,
            'school_year_id'  => $request->school_year_id,
            'level_id'        => $request->level_id,
            'grade_id'        => $request->grade_id,
            'section_id'      => $request->section_id,
            'fecha_matricula' => $request->fecha_matricula,
            'monto_matricula' => $request->monto_matricula,
            'estado'          => 'pagado',
        ]);

        // =========================
        // REGISTRAR PAGO MATRÍCULA (DESPUÉS)
        // =========================
        $conceptMatricula = \App\Models\PaymentConcept::where('nombre', 'MATRÍCULA')->firstOrFail();

        Payment::create([
            'enrollment_id'      => $enrollment->id,
            'payment_concept_id' => $conceptMatricula->id,
            'periodo'            => null, // Matrícula NO usa periodo
            'monto'              => $request->monto_matricula,
            'descuento'          => 0,
            'fecha_pago'         => now(),
            'metodo_pago'        => 'efectivo',
            'estado'             => 'pagado',
        ]);

        // =========================
        // GENERAR VOUCHER
        // =========================
        $this->generarVoucherFPDF($enrollment);

        return redirect()
            ->route('enrollments.voucher', $enrollment->id)
            ->with('success', 'Matrícula registrada correctamente.');
    }
    /* =========================
       PDF + QR
    ========================== */
    private function generarVoucherFPDF(Enrollment $enrollment)
    {
        $student = $enrollment->student;

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
        LOGO INSTITUCIÓN
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
        $pdf->Cell(
            0,
            8,
            utf8_decode('COMPROBANTE DE MATRÍCULA ' . $enrollment->schoolYear->anio),
            0,
            1
        );

        /* ======================
        DATOS ESTUDIANTE
        ====================== */
        $pdf->Ln(20);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, utf8_decode('DATOS DEL ESTUDIANTE'), 0, 1);

        $pdf->SetFont('Arial', '', 11);
        $pdf->Cell(60, 8, utf8_decode('Nombre completo:'), 1);
        $pdf->Cell(
            0,
            8,
            utf8_decode($student->nombres . ' ' . $student->apellido_paterno . ' ' . $student->apellido_materno),
            1,
            1
        );

        $pdf->Cell(60, 8, utf8_decode('DNI:'), 1);
        $pdf->Cell(0, 8, $student->dni, 1, 1);

        $pdf->Cell(60, 8, utf8_decode('Nivel:'), 1);
        $pdf->Cell(0, 8, utf8_decode($enrollment->level->nombre), 1, 1);

        $pdf->Cell(60, 8, utf8_decode('Grado:'), 1);
        $pdf->Cell(0, 8, utf8_decode($enrollment->grade->nombre), 1, 1);

        $pdf->Cell(60, 8, utf8_decode('Sección:'), 1);
        $pdf->Cell(0, 8, utf8_decode($enrollment->section->nombre), 1, 1);

        $pdf->Cell(60, 8, utf8_decode('Fecha matrícula:'), 1);
        $pdf->Cell(0, 8, $enrollment->fecha_matricula, 1, 1);

        $pdf->Cell(60, 8, utf8_decode('Monto:'), 1);
        $pdf->Cell(
            0,
            8,
            'S/. ' . number_format($enrollment->monto_matricula, 2),
            1,
            1
        );

        /* ======================
        FOTO ESTUDIANTE Y QR LADO A LADO
        ====================== */
        $yStart = $pdf->GetY() + 15;

        // Tamaños
        $fotoWidth = 40;
        $fotoHeight = 50;
        $qrWidth = 40;
        $qrHeight = 40;

        // Posiciones X
        $xFoto = 25;
        $xQR = 120;

        // Foto del estudiante
        $fotoPath = null;
        if (!empty($student->photo_path)) {
            $fotoPath = storage_path('app/public/' . $student->photo_path);
        }
        if (!$fotoPath || !file_exists($fotoPath)) {
            $fotoPath = public_path('img/default-user.jpg');
        }
        $pdf->Image($fotoPath, $xFoto, $yStart, $fotoWidth, $fotoHeight);
        $pdf->SetXY($xFoto, $yStart + $fotoHeight + 2);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($fotoWidth, 6, utf8_decode('Foto del estudiante'), 0, 0, 'C');

        // QR
        if (file_exists($qrPath)) {
            $pdf->Image($qrPath, $xQR, $yStart, $qrWidth, $qrHeight);
        }
        $pdf->SetXY($xQR, $yStart + $qrHeight + 2);
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell($qrWidth, 6, utf8_decode('Código QR'), 0, 0, 'C');

        // Ajustar Y del PDF
        $pdf->SetY(max($yStart + $fotoHeight + 10, $yStart + $qrHeight + 10));

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
            'schoolYear'
        ])->findOrFail($id);

        $students = Student::where('estado', 'activo')
            ->select('id', 'nombres', 'apellido_paterno', 'apellido_materno', 'dni')
            ->get();

        $levels = Level::select('id', 'nombre')->get();

        // 👌 Cargar TODOS los grados (como en create)
        $grades = Grade::select('id', 'nombre', 'level_id')->get();

        // 👌 Secciones filtradas según la matrícula actual
        $sections = Section::where('school_year_id', $enrollment->school_year_id)
            ->where('grade_id', $enrollment->grade_id)
            ->where('activo', true)
            ->get();

        $schoolYears = SchoolYear::select('id', 'nombre')->get();

        return view(
            'enrollments.edit',
            compact(
                'enrollment',
                'students',
                'levels',
                'grades',
                'sections',
                'schoolYears'
            )
        );
    }

    /* =========================
       ACTUALIZAR MATRÍCULA
    ========================== */
    public function update(Request $request, $id)
    {
        $enrollment = Enrollment::findOrFail($id);

        /* =========================
        VALIDACIÓN
        ========================== */
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
            'monto_matricula' => 'required|numeric|min:0',
            'estado'          => 'required|in:pendiente,pagado,validado',
        ]);

        /* =========================
        EVITAR MATRÍCULA DUPLICADA
        ========================== */
        $duplicado = Enrollment::where('student_id', $request->student_id)
            ->where('school_year_id', $request->school_year_id)
            ->where('estado', 'pagado')
            ->where('id', '!=', $enrollment->id)
            ->first();

        if ($duplicado) {
            return back()
                ->withInput()
                ->withErrors([
                    'student_id' => 'El estudiante ya está matriculado en este año escolar.'
                ]);
        }

        /* =========================
        ACTUALIZAR MATRÍCULA
        ========================== */
        $enrollment->update([
            'student_id'      => $request->student_id,
            'school_year_id'  => $request->school_year_id,
            'level_id'        => $request->level_id,
            'grade_id'        => $request->grade_id,
            'section_id'      => $request->section_id,
            'fecha_matricula' => $request->fecha_matricula,
            'monto_matricula' => $request->monto_matricula,
            'estado'          => $request->estado,
        ]);

        /* =========================
        SINCRONIZAR PAGO MATRÍCULA
        ========================== */
        $pagoMatricula = Payment::where('enrollment_id', $enrollment->id)
            ->whereHas('paymentConcept', function ($q) {
                $q->where('nombre', 'MATRÍCULA');
            })
            ->first();

        if ($pagoMatricula) {
            $pagoMatricula->update([
                'monto'  => $request->monto_matricula,
                'estado' => $request->estado === 'pagado' ? 'pagado' : 'pendiente',
            ]);
        }

        /* =========================
        REGENERAR VOUCHER
        ========================== */
        if ($request->estado === 'pagado') {
            $this->generarVoucherFPDF($enrollment);
        }

        return redirect()
            ->route('enrollments.index')
            ->with('success', 'Matrícula actualizada correctamente.');
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