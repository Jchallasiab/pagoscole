<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();

            // Identificación
            $table->string('dni', 8)->unique();

            // Datos personales
            $table->string('nombres');
            $table->string('apellido_paterno');
            $table->string('apellido_materno');

            // Foto del estudiante (ruta)
            $table->string('photo_path')->nullable();
            // ej: students/74859632.jpg

            // Contacto
            $table->string('email')->nullable();
            $table->string('celular', 9);
            $table->string('direccion');

            // Apoderado
            $table->string('nombre_apoderado')->nullable();
            $table->string('celular_apoderado', 9)->nullable();

            // Estado del alumno
            $table->enum('estado', ['activo', 'inactivo', 'retirado'])
                  ->default('activo');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
