<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'payment_concept_id',
        'periodo', // ✅ CORREGIDO (antes mes)
        'monto',
        'descuento',
        'fecha_pago',
        'metodo_pago',
        'voucher',
        'estado',
    ];

    /** 🔹 Un pago pertenece a una matrícula */
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    /** 🔹 Concepto de pago */
    public function paymentConcept()
    {
        return $this->belongsTo(PaymentConcept::class, 'payment_concept_id');
    }

    /** 🔹 URL pública del voucher */
    public function getVoucherUrlAttribute()
    {
        return $this->voucher
            ? asset('storage/' . $this->voucher)
            : null;
    }

    /** 🔹 Estado legible */
    public function getEstadoLabelAttribute()
    {
        return match ($this->estado) {
            'pagado'   => 'Pagado',
            'validado' => 'Validado',
            default    => 'Pendiente',
        };
    }

    /** 🔹 Monto final con descuento */
    public function getMontoFinalAttribute()
    {
        return max(0, $this->monto - ($this->descuento ?? 0));
    }
    public function concept()
    {
        return $this->paymentConcept();
    }

}