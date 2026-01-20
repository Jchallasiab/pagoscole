<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_concepts', function (Blueprint $table) {
            $table->id();

            // Nombre del concepto
            $table->string('nombre'); // Matrícula, Mensualidad, etc.

            // Si se cobra mensualmente
            $table->boolean('es_mensual')->default(false);

            // Descripción opcional
            $table->string('descripcion')->nullable();

            // Estado del concepto
            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_concepts');
    }
};
