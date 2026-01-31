<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BarangController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PaymentConceptController;
use App\Http\Controllers\LevelController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\SchoolYearController;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

// ================== AUTH ==================
Route::get('/login', [AuthController::class, 'index'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'authenticate']);
Route::get('/register', [AuthController::class, 'register']);
Route::post('/register', [AuthController::class, 'process']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

// ================== DASHBOARD ==================
Route::get('/', [DashboardController::class, 'index'])->middleware('auth');
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('auth');


// ================== RUTAS COMPARTIDAS (ADMIN + SECRETARIA) ==================
Route::middleware(['auth', 'role:admin|secretaria'])->group(function () {

    // 👉 Gestión de estudiantes, matrículas y pagos (ambos pueden)
    Route::get('/students/search', [StudentController::class, 'search'])->name('students.search');
    Route::resource('/students', StudentController::class);
    Route::get('/enrollments/sections', [EnrollmentController::class, 'getSections'])
    ->name('enrollments.sections');
    Route::resource('/enrollments', EnrollmentController::class);
    Route::get('/voucher/{filename}', [PaymentController::class, 'verVoucher'])
    ->name('voucher.view');


    Route::resource('/payments', PaymentController::class);
    Route::get('/enrollments/{id}/voucher', [EnrollmentController::class, 'voucher'])->name('enrollments.voucher');
    Route::get('/enrollments/export/excel',[EnrollmentController::class, 'exportExcel'])->name('enrollments.export.excel');
    Route::get('/mostrar-foto/{filename}', function ($filename) {
        $path = storage_path('app/public/students/' . $filename);

        if (!file_exists($path)) {
            abort(404);
        }

        $file = file_get_contents($path);
        $type = mime_content_type($path);

        return response($file, 200)->header('Content-Type', $type);
    })->name('mostrar.foto');



    // 👉 Funciones AJAX y PDF (permitidas también para secretaría)
    Route::get('buscar-matricula/{dni}', [PaymentController::class, 'buscarPorDni'])
        ->name('payments.buscar.matricula');
    Route::get('payments/buscar/{dni}', [PaymentController::class, 'buscarPagosPorDni'])
        ->name('payments.buscar');
    Route::get('payments/{payment}/pdf', [PaymentController::class, 'pdf'])
        ->name('payments.pdf');
});

// ================== SOLO ADMIN ==================
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('/payment_concepts', PaymentConceptController::class);
    Route::resource('/grades', GradeController::class);
    Route::resource('/levels', LevelController::class);
    Route::resource('/sections', SectionController::class);
    Route::resource('/school_years', SchoolYearController::class);
    Route::resource('/users', UserController::class);


    // Reportes
    Route::get('/reportes/pagos-por-curso', [PaymentReportController::class, 'index'])
        ->name('reports.payments');
    Route::get('reportes/pagos-por-curso/excel', [PaymentReportController::class, 'exportExcel'])
        ->name('reports.payments.excel');
});
