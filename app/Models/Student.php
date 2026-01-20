<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'dni',
        'nombres',
        'apellido_paterno',
        'apellido_materno',
        'photo_path',
        'email',
        'celular',
        'direccion',
        'nombre_apoderado',
        'celular_apoderado',
        'estado',
    ];

    /**
     * 🔹 Un alumno puede tener muchas matrículas
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * 🔹 Matrícula actual (último año escolar)
     */
    public function currentEnrollment()
    {
        return $this->hasOne(Enrollment::class)->latestOfMany();
    }

    /**
     * 🔹 Nombre completo (para vistas y reportes)
     */
    public function getNombreCompletoAttribute()
    {
        return "{$this->nombres} {$this->apellido_paterno} {$this->apellido_materno}";
    }

    /**
     * 🔹 URL de la foto del estudiante
     */
    public function getPhotoUrlAttribute()
    {
        return $this->photo_path
            ? asset('storage/' . $this->photo_path)
            : asset('images/default-student.png');
    }
}
