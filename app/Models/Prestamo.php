<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Prestamo',
    required: ['solicitud_id', 'monto_aprobado', 'tasa_interes', 'plazo_meses'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'solicitud_id', type: 'integer', example: 2),
        new OA\Property(property: 'monto_aprobado', type: 'number', format: 'float', example: 15000.00),
        new OA\Property(property: 'fecha_aprobacion', type: 'string', format: 'date-time', example: '2025-11-10T10:00:00Z'),
        new OA\Property(property: 'tasa_interes', type: 'number', format: 'float', example: 12.5),
        new OA\Property(property: 'plazo_meses', type: 'integer', example: 12),
        new OA\Property(property: 'saldo_actual', type: 'number', format: 'float', example: 15000.00),
        new OA\Property(property: 'estado', type: 'string', example: 'ACTIVO'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class Prestamo extends Model
{
    use HasFactory;

    protected $fillable = [
        'solicitud_id',
        'monto_aprobado',
        'fecha_aprobacion',
        'tasa_interes',
        'plazo_meses',
        'saldo_actual',
        'estado',
    ];

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }
}
