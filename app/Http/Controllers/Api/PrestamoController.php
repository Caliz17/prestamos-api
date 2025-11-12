<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prestamo;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Préstamos', description: 'Gestión de préstamos y su relación con solicitudes y pagos')]
class PrestamoController extends Controller
{
    #[OA\Get(
        path: '/api/prestamos',
        summary: 'Listar préstamos',
        description: 'Obtiene todos los préstamos registrados (requiere autenticación).',
        tags: ['Préstamos'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Listado de préstamos obtenido correctamente'),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function index()
    {
        $prestamos = Prestamo::with('solicitud.cliente')->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $prestamos,
        ], 200);
    }

    #[OA\Post(
        path: '/api/prestamos',
        summary: 'Crear préstamo (a partir de solicitud aprobada)',
        description: 'Crea un nuevo préstamo basado en una solicitud previamente aprobada.',
        tags: ['Préstamos'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['solicitud_id', 'monto_aprobado', 'tasa_interes', 'plazo_meses'],
                properties: [
                    new OA\Property(property: 'solicitud_id', type: 'integer', example: 2),
                    new OA\Property(property: 'monto_aprobado', type: 'number', example: 15000.00),
                    new OA\Property(property: 'tasa_interes', type: 'number', example: 12.5),
                    new OA\Property(property: 'plazo_meses', type: 'integer', example: 12),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Préstamo creado correctamente'),
            new OA\Response(response: 404, description: 'Solicitud no encontrada'),
            new OA\Response(response: 400, description: 'Solicitud no aprobada')
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'solicitud_id' => 'required|exists:solicitudes,id',
            'monto_aprobado' => 'required|numeric|min:0',
            'tasa_interes' => 'required|numeric|min:0',
            'plazo_meses' => 'required|integer|min:1',
        ]);

        $solicitud = Solicitud::find($validated['solicitud_id']);

        if ($solicitud->estado !== 'APROBADO') {
            return response()->json([
                'status' => 'error',
                'message' => 'La solicitud debe estar en estado APROBADO para generar un préstamo.'
            ], 400);
        }

        $prestamo = Prestamo::create([
            'solicitud_id' => $solicitud->id,
            'monto_aprobado' => $validated['monto_aprobado'],
            'fecha_aprobacion' => now(),
            'tasa_interes' => $validated['tasa_interes'],
            'plazo_meses' => $validated['plazo_meses'],
            'saldo_actual' => $validated['monto_aprobado'],
            'estado' => 'ACTIVO',
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $prestamo,
        ], 201);
    }

    #[OA\Get(
        path: '/api/prestamos/{id}',
        summary: 'Ver préstamo específico',
        description: 'Obtiene los datos detallados de un préstamo por su ID.',
        tags: ['Préstamos'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Préstamo encontrado'),
            new OA\Response(response: 404, description: 'Préstamo no encontrado')
        ]
    )]
    public function show($id)
    {
        $prestamo = Prestamo::with(['solicitud.cliente', 'pagos'])->find($id);

        if (!$prestamo) {
            return response()->json(['message' => 'Préstamo no encontrado'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $prestamo,
        ], 200);
    }

    #[OA\Put(
        path: '/api/prestamos/{id}',
        summary: 'Actualizar préstamo',
        description: 'Actualiza el estado o datos de un préstamo existente.',
        tags: ['Préstamos'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Préstamo actualizado correctamente'),
            new OA\Response(response: 404, description: 'Préstamo no encontrado')
        ]
    )]
    public function update(Request $request, $id)
    {
        $prestamo = Prestamo::find($id);

        if (!$prestamo) {
            return response()->json(['message' => 'Préstamo no encontrado'], 404);
        }

        $prestamo->update($request->only(['saldo_actual', 'estado', 'tasa_interes', 'plazo_meses']));

        return response()->json(['status' => 'success', 'data' => $prestamo], 200);
    }

    #[OA\Delete(
        path: '/api/prestamos/{id}',
        summary: 'Eliminar préstamo',
        description: 'Elimina un préstamo existente (solo si no tiene pagos asociados).',
        tags: ['Préstamos'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1))
        ],
        responses: [
            new OA\Response(response: 204, description: 'Préstamo eliminado correctamente'),
            new OA\Response(response: 400, description: 'El préstamo tiene pagos y no puede eliminarse')
        ]
    )]
    public function destroy($id)
    {
        $prestamo = Prestamo::with('pagos')->find($id);

        if (!$prestamo) {
            return response()->json(['message' => 'Préstamo no encontrado'], 404);
        }

        if ($prestamo->pagos->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'El préstamo tiene pagos registrados y no puede eliminarse.'
            ], 400);
        }

        $prestamo->delete();
        return response()->json(['message' => 'Préstamo eliminado correctamente'], 204);
    }
}
