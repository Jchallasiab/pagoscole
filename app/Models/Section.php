<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = [
        'school_year_id',
        'grade_id',
        'nombre',
        'capacidad',
        'activo',
    ];

    // 📆 Año escolar
    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class);
    }

    // 🎓 Grado
    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    // 🔁 Acceso directo al nivel
    public function level()
    {
        return $this->grade->level();
    }
}
