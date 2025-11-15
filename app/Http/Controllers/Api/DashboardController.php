<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\Prestamo;
use App\Models\Pago;
use OpenApi\Attributes as OA;

class DashboardController extends Controller
{
    #[OA\Get(
        path: '/api/dashboard',
        summary: 'Obtener datos del dashboard',
        description: 'Proporciona un resumen de datos clave para el dashboard (requiere autenticación).',
        tags: ['Dashboard'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200, 
                description: 'Datos del dashboard obtenidos correctamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(property: 'data', type: 'object', properties: [
                            new OA\Property(property: 'clientes', type: 'integer', example: 150),
                            new OA\Property(property: 'prestamos_activos', type: 'integer', example: 45),
                            new OA\Property(property: 'ingresos_mensuales', type: 'number', format: 'float', example: 12500.75),
                            new OA\Property(property: 'prestamos_recientes', type: 'array', items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer', example: 1),
                                    new OA\Property(property: 'monto_aprobado', type: 'number', format: 'float', example: 15000.50),
                                    new OA\Property(property: 'cliente_nombre', type: 'string', example: 'Juan Pérez'),
                                    new OA\Property(property: 'estado', type: 'string', example: 'ACTIVO'),
                                    new OA\Property(property: 'fecha_aprobacion', type: 'string', format: 'date-time'),
                                ]
                            ))
                        ])
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function index()
    {
        try {
            // Estadísticas básicas
            $clientes = Cliente::count();
            $prestamosActivos = Prestamo::where('estado', 'ACTIVO')->count();
            $ingresosMensuales = Pago::whereMonth('created_at', now()->month)->sum('monto_pagado');
            
            // Préstamos recientes con solo los campos necesarios
            $prestamosRecientes = Prestamo::with(['solicitud.cliente:id,primer_nombre,segundo_nombre,primer_apellido'])
                ->select(['id', 'monto_aprobado', 'fecha_aprobacion', 'estado', 'solicitud_id'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($prestamo) {
                    return [
                        'id' => $prestamo->id,
                        'monto_aprobado' => (float) $prestamo->monto_aprobado,
                        'cliente_nombre' => $prestamo->solicitud->cliente ? 
                            $prestamo->solicitud->cliente->primer_nombre . ' ' . 
                            $prestamo->solicitud->cliente->primer_apellido : 'N/A',
                        'estado' => $prestamo->estado,
                        'fecha_aprobacion' => $prestamo->fecha_aprobacion,
                    ];
                });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'clientes' => $clientes,
                    'prestamos_activos' => $prestamosActivos,
                    'ingresos_mensuales' => (float) $ingresosMensuales,
                    'prestamos_recientes' => $prestamosRecientes,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener datos del dashboard: ' . $e->getMessage()
            ], 500);
        }
    }
}