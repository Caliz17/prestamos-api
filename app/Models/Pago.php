<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Pago',
    required: ['prestamo_id', 'monto_pagado', 'metodo_pago'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'prestamo_id', type: 'integer', example: 2),
        new OA\Property(property: 'fecha_pago', type: 'string', format: 'date-time', example: '2025-11-12T10:00:00Z'),
        new OA\Property(property: 'monto_pagado', type: 'number', format: 'float', example: 1200.50),
        new OA\Property(property: 'metodo_pago', type: 'string', example: 'EFECTIVO'),
        new OA\Property(property: 'observaciones', type: 'string', example: 'Cobro realizado en ruta norte.'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class Pago extends Model
{
    use HasFactory;

    protected $fillable = [
        'prestamo_id',
        'fecha_pago',
        'monto_pagado',
        'metodo_pago',
        'observaciones',
    ];

    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class);
    }
}
