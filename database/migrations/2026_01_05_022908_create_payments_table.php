<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Relación con la matrícula (alumno + año)
            $table->foreignId('enrollment_id')
                  ->constrained('enrollments')
                  ->cascadeOnDelete();

            // Concepto del pago (Matrícula, Mensualidad, etc.)
            $table->foreignId('payment_concept_id')
                  ->constrained('payment_concepts')
                  ->cascadeOnDelete();

            // Periodo SOLO para conceptos mensuales (ej: 2026-03)
            $table->string('periodo', 7)->nullable();

            // Monto
            $table->decimal('monto', 8, 2);

            // Descuento
            $table->decimal('descuento', 8, 2)->default(0);

            // Fecha del pago
            $table->date('fecha_pago')->nullable();

            // Método de pago
            $table->enum('metodo_pago', [
                'efectivo',
                'yape',
                'plin',
                'transferencia'
            ])->nullable();

            // Voucher / comprobante
            $table->string('voucher')->nullable();

            // Estado del pago
            $table->enum('estado', ['pendiente', 'pagado', 'validado'])
                  ->default('pendiente');

            /**
             * Reglas:
             * - Matrícula: periodo = NULL
             * - Mensualidad: periodo = YYYY-MM
             */
            $table->unique(['enrollment_id', 'payment_concept_id', 'periodo']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
