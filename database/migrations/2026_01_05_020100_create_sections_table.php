<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();

            // 📆 Año escolar
            $table->foreignId('school_year_id')
                  ->constrained('school_years')
                  ->cascadeOnDelete();

            // 🎓 Grado
            $table->foreignId('grade_id')
                  ->constrained('grades')
                  ->cascadeOnDelete();

            // 🅰️ Nombre sección (A, B, C)
            $table->string('nombre', 10);

            // 👥 Capacidad
            $table->integer('capacidad')->nullable();

            // ✅ Estado
            $table->boolean('activo')->default(true);

            $table->timestamps();

            // ❗ Evitar duplicados por año + grado
            $table->unique(['school_year_id', 'grade_id', 'nombre']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
