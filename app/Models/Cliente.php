<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'dpi',
        'nit',
        'fecha_nacimiento',
        'direccion',
        'correo',
        'telefono',
        'usuario_crea',
        'usuario_actualiza',
    ];
}
