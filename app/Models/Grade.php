<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Enrollment;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'level_id',
        'activo',
    ];

    /** Relación con nivel */
    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    /** ✅ Relación con matrículas */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }
}
