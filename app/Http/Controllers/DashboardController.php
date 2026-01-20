<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Enrollment;
use App\Models\Payment;
use App\Models\Level;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Total estudiantes
        $totalStudents = Student::count();

        // Total matrículas
        $totalEnrollments = Enrollment::count();

        // Pagos pendientes
        $pendingPayments = Payment::where('estado', 'pendiente')->count();

        // Niveles con sus grados y cantidad de matriculados por grado
        $levels = Level::where('activo', true)
            ->with(['grades' => function($q) {
                $q->where('activo', true)
                  ->withCount('enrollments');
            }])
            ->get();

        return view('dashboard.dashboard', compact(
            'totalStudents',
            'totalEnrollments',
            'pendingPayments',
            'levels'
        ));
    }
}
