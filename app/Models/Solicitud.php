<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Solicitud',
    required: ['cliente_id', 'monto_solicitado', 'plazo_meses', 'tasa_interes'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 10),
        new OA\Property(property: 'cliente_id', type: 'integer', example: 1),
        new OA\Property(property: 'monto_solicitado', type: 'number', format: 'float', example: 15000.50),
        new OA\Property(property: 'plazo_meses', type: 'integer', example: 12),
        new OA\Property(property: 'tasa_interes', type: 'number', format: 'float', example: 12.5),
        new OA\Property(property: 'estado', type: 'string', example: 'EN PROCESO'),
        new OA\Property(property: 'observaciones', type: 'string', example: 'Solicitud para compra de vehículo.'),
        new OA\Property(property: 'usuario_crea', type: 'string', example: 'admin'),
        new OA\Property(property: 'usuario_actualiza', type: 'string', example: 'operador1'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'cliente', ref: '#/components/schemas/Cliente'),
    ]
)]
class Solicitud extends Model
{
    use HasFactory;

    protected $table = 'solicitudes';
    public $timestamps = false;

    protected $fillable = [
        'cliente_id',
        'monto_solicitado',
        'plazo_meses',
        'tasa_interes',
        'estado',
        'observaciones',
        'usuario_crea',
        'usuario_actualiza',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function prestamo()
    {
        return $this->hasOne(Prestamo::class);
    }
}
