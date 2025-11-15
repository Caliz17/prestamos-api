<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Cliente',
    title: 'Cliente',
    description: 'Modelo que representa un cliente en el sistema',
    required: ['id', 'primer_nombre', 'primer_apellido', 'dpi', 'nit'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'primer_nombre', type: 'string', example: 'Juan'),
        new OA\Property(property: 'segundo_nombre', type: 'string', example: 'Carlos'),
        new OA\Property(property: 'primer_apellido', type: 'string', example: 'Pérez'),
        new OA\Property(property: 'segundo_apellido', type: 'string', example: 'López'),
        new OA\Property(property: 'dpi', type: 'string', example: '1234567890101'),
        new OA\Property(property: 'nit', type: 'string', example: '1234567-8'),
        new OA\Property(property: 'fecha_nacimiento', type: 'string', format: 'date', example: '1990-05-10'),
        new OA\Property(property: 'direccion', type: 'string', example: 'Ciudad de Guatemala, Zona 1'),
        new OA\Property(property: 'correo', type: 'string', format: 'email', example: 'juan.perez@example.com'),
        new OA\Property(property: 'telefono', type: 'string', example: '5555-1234'),
        new OA\Property(property: 'solicitudes', type: 'array', items: new OA\Items(ref: '#/components/schemas/Solicitud')),
    ]
)]
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

    // AGREGAR ESTAS RELACIONES:

    /**
     * Relación con las solicitudes del cliente
     */
    public function solicitudes()
    {
        return $this->hasMany(Solicitud::class);
    }

    /**
     * Relación con préstamos a través de solicitudes
     */
    public function prestamos()
    {
        return $this->hasManyThrough(Prestamo::class, Solicitud::class);
    }

    /**
     * Scope para clientes con préstamos activos
     */
    public function scopeConPrestamos($query)
    {
        return $query->whereHas('solicitudes.prestamo');
    }

    /**
     * Scope para clientes nuevos del mes
     */
    public function scopeNuevosEsteMes($query)
    {
        return $query->whereMonth('created_at', now()->month);
    }
}