<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentConcept extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'descripcion',
        'es_mensual',
        'activo',
        'school_year_id',
    ];

    /** 🔹 Relación con pagos */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /** 🔹 Relación opcional con año escolar */
    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    /** 🔹 Scopes para uso rápido */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeMensuales($query)
    {
        return $query->where('es_mensual', true);
    }
}
