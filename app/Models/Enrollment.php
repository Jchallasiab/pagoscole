<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'school_year_id',
        'level_id',
        'grade_id',
        'section_id',
        'fecha_matricula',
        'monto_matricula',
        'voucher_matricula',
        'estado',
    ];

    /**
     * 🔹 La matrícula pertenece a un estudiante
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * 🔹 Año escolar
     */
    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    /**
     * 🔹 Nivel (Inicial / Primaria / Secundaria)
     */
    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    /**
     * 🔹 Grado
     */
    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    /**
     * 🔹 Sección
     */
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * 🔹 Pagos asociados a esta matrícula
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * 🔹 URL pública del voucher de matrícula
     */
    public function getVoucherUrlAttribute()
    {
        return $this->voucher_matricula
            ? asset('storage/' . $this->voucher_matricula)
            : null;
    }

    /**
     * 🔹 Estado legible (para vistas)
     */
    public function getEstadoLabelAttribute()
    {
        return match ($this->estado) {
            'pagado'   => 'Pagado',
            'validado' => 'Validado',
            default    => 'Pendiente',
        };
    }
}
