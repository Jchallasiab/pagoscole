<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id_user';

    /**
     * Los campos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * Oculta campos sensibles en las respuestas.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Convierte atributos a tipos nativos.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
