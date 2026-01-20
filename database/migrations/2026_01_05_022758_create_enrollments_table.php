<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();

            // Alumno
            $table->foreignId('student_id')
                  ->constrained('students')
                  ->cascadeOnDelete();

            // Año escolar
            $table->foreignId('school_year_id')
                  ->constrained('school_years')
                  ->cascadeOnDelete();

            // Nivel / Grado / Sección (AQUÍ van correctamente)
            $table->foreignId('level_id')
                  ->constrained('levels')
                  ->cascadeOnDelete();

            $table->foreignId('grade_id')
                  ->constrained('grades')
                  ->cascadeOnDelete();

            $table->foreignId('section_id')
                  ->nullable()
                  ->constrained('sections')
                  ->nullOnDelete();

            // Datos de matrícula
            $table->date('fecha_matricula');

            // Monto de matrícula (informativo)
            $table->decimal('monto_matricula', 8, 2)->default(0);

            // Voucher de matrícula
            $table->string('voucher_matricula')->nullable();

            // Estado del proceso
            $table->enum('estado', ['pendiente', 'pagado', 'validado'])
                  ->default('pendiente');

            // 🔐 Regla fuerte:
            // Un alumno SOLO puede tener una matrícula por año escolar
            $table->unique(['student_id', 'school_year_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
