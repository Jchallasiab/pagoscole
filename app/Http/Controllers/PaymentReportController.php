<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Models\Grade;
use App\Models\Section;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Support\Str;

class PaymentReportController extends Controller
{
    public function index(Request $request)
    {
        $year       = $request->year ?? now()->year;
        $level_id   = $request->level_id;
        $grade_id   = $request->grade_id;
        $section_id = $request->section_id;

        $levels     = Level::orderBy('nombre')->get();
        $gradesAll  = Grade::select('id','nombre','level_id')->orderBy('nombre')->get();
        $sectionsAll= Section::select('id','nombre','grade_id')->orderBy('nombre')->get();

        $enrollments = collect();

        $meses = [
            1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
            7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'
        ];

        if ($level_id && $grade_id && $section_id) {
            $enrollments = Enrollment::with([
                'student',
                'payments' => function ($q) use ($year) {
                    $q->whereHas('paymentConcept', fn($qc) =>
                        $qc->where('nombre', 'Mensualidad')
                    )
                    ->where('estado', 'pagado')
                    ->where('periodo', 'like', "$year-%");
                },
                'level','grade','section'
            ])
            ->where(compact('level_id','grade_id','section_id'))
            ->whereYear('fecha_matricula', $year)
            ->get();
        }

        return view('reports.payments_by_class', compact(
            'levels','gradesAll','sectionsAll',
            'enrollments','year','meses',
            'level_id','grade_id','section_id'
        ));
    }

    public function exportExcel(Request $request)
    {
        $request->validate([
            'level_id'=>'required',
            'grade_id'=>'required',
            'section_id'=>'required',
            'year'=>'required'
        ]);

        $year = $request->year;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 🟢 Encabezado
        $sheet->mergeCells('A1:N1');
        $sheet->setCellValue('A1', ''); // Lo llenaremos después
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

        $meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        $sheet->fromArray(array_merge(['#','Estudiante'],$meses), null, 'A2');

        $row = 3;
        $i = 1;
        $firstEnrollment = null;

        Enrollment::with([
            'student',
            'payments' => function ($q) use ($year) {
                $q->whereHas('paymentConcept', fn($qc) =>
                    $qc->where('nombre','Mensualidad')
                )
                ->where('estado','pagado')
                ->where('periodo','like',"$year-%");
            },
            'level','grade','section'
        ])
        ->where('level_id',$request->level_id)
        ->where('grade_id',$request->grade_id)
        ->where('section_id',$request->section_id)
        ->chunk(100, function($enrollmentsChunk) use (&$sheet, &$row, &$i, &$firstEnrollment) {

            foreach ($enrollmentsChunk as $enrollment) {

                // Guardamos el primer registro para encabezado
                if (!$firstEnrollment) {
                    $firstEnrollment = $enrollment;
                }

                $fila = [
                    $i++,
                    "{$enrollment->student->nombres} {$enrollment->student->apellido_paterno} {$enrollment->student->apellido_materno}"
                ];

                foreach(range(1,12) as $m){
                    $periodo = sprintf('%04d-%02d', $enrollment->fecha_matricula->year ?? now()->year, $m);
                    $fila[] = $enrollment->payments->firstWhere('periodo',$periodo) ? '✔' : '✘';
                }

                $sheet->fromArray($fila, null, "A{$row}");

                foreach(range(3,14) as $col){
                    $c = Coordinate::stringFromColumnIndex($col);
                    $v = $sheet->getCell("$c$row")->getValue();
                    $sheet->getStyle("$c$row")->getFont()->getColor()
                        ->setRGB($v === '✔' ? '008000' : 'FF0000');
                }

                $row++;
            }
        });

        if (!$firstEnrollment) {
            return back()->with('error','No hay datos para exportar');
        }

        // 🟢 Ahora sí llenamos encabezado con datos reales
        $level   = $firstEnrollment->level->nombre ?? '-';
        $grade   = $firstEnrollment->grade->nombre ?? '-';
        $section = $firstEnrollment->section->nombre ?? '-';
        $sheet->setCellValue('A1', "$level - $grade - Sección $section - $year");

        foreach(range('A','N') as $col){
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $file = storage_path(
            'reporte_pagos_'.Str::slug("$level-$grade-$section")."_$year.xlsx"
        );

        (new Xlsx($spreadsheet))->save($file);
        return response()->download($file)->deleteFileAfterSend(true);
    }
}
