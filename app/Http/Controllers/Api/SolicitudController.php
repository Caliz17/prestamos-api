<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Solicitud;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Solicitudes', description: 'Gestión de solicitudes de préstamos')]
class SolicitudController extends Controller
{
    // ==========================================
    // GET /api/solicitudes
    // ==========================================
    #[OA\Get(
        path: '/api/solicitudes',
        summary: 'Obtener lista de solicitudes',
        description: 'Devuelve todas las solicitudes de préstamo registradas (requiere autenticación).',
        tags: ['Solicitudes'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de solicitudes obtenida correctamente.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Solicitud')
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'No autorizado'),
            new OA\Response(response: 500, description: 'Error interno del servidor')
        ]
    )]
    public function index()
    {
        try {
            $solicitudes = Solicitud::with('cliente')->orderBy('id', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'data' => $solicitudes,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener las solicitudes: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ==========================================
    // POST /api/solicitudes
    // ==========================================
    #[OA\Post(
        path: '/api/solicitudes',
        summary: 'Crear nueva solicitud',
        description: 'Registra una nueva solicitud de préstamo para un cliente (requiere autenticación).',
        tags: ['Solicitudes'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['cliente_id', 'monto_solicitado', 'plazo_meses', 'tasa_interes'],
                properties: [
                    new OA\Property(property: 'cliente_id', type: 'integer', example: 1),
                    new OA\Property(property: 'monto_solicitado', type: 'number', format: 'float', example: 15000.50),
                    new OA\Property(property: 'plazo_meses', type: 'integer', example: 12),
                    new OA\Property(property: 'tasa_interes', type: 'number', format: 'float', example: 12.5),
                    new OA\Property(property: 'observaciones', type: 'string', example: 'Cliente solicita préstamo para vehículo'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Solicitud creada correctamente'),
            new OA\Response(response: 422, description: 'Error de validación'),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'monto_solicitado' => 'required|numeric|min:1',
            'plazo_meses' => 'required|integer|min:1',
            'tasa_interes' => 'required|numeric|min:0',
            'observaciones' => 'nullable|string|max:255',
        ]);

        $validated['estado'] = 'EN PROCESO';
        $validated['usuario_crea'] = auth()->user()->name ?? 'sistema';

        $solicitud = Solicitud::create($validated);

        return response()->json([
            'status' => 'success',
            'data' => $solicitud,
        ], 201);
    }

    // ==========================================
    // GET /api/solicitudes/{id}
    // ==========================================
    #[OA\Get(
        path: '/api/solicitudes/{id}',
        summary: 'Obtener solicitud específica',
        description: 'Obtiene los detalles de una solicitud de préstamo específica (requiere autenticación).',
        tags: ['Solicitudes'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1),
                description: 'ID de la solicitud a consultar'
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Solicitud encontrada'),
            new OA\Response(response: 404, description: 'Solicitud no encontrada'),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function show($id)
    {
        $solicitud = Solicitud::with('cliente')->find($id);

        if (!$solicitud) {
            return response()->json(['message' => 'Solicitud no encontrada'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $solicitud], 200);
    }

    // ==========================================
    // PUT /api/solicitudes/{id}
    // ==========================================
    #[OA\Put(
        path: '/api/solicitudes/{id}',
        summary: 'Actualizar solicitud',
        description: 'Modifica una solicitud existente (por ejemplo, aprobar o rechazar).',
        tags: ['Solicitudes'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Solicitud actualizada correctamente'),
            new OA\Response(response: 404, description: 'Solicitud no encontrada'),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function update(Request $request, $id)
    {
        $solicitud = Solicitud::find($id);

        if (!$solicitud) {
            return response()->json(['message' => 'Solicitud no encontrada'], 404);
        }

        $validated = $request->validate([
            'monto_solicitado' => 'nullable|numeric|min:1',
            'plazo_meses' => 'nullable|integer|min:1',
            'tasa_interes' => 'nullable|numeric|min:0',
            'estado' => 'nullable|string|in:EN PROCESO,APROBADO,RECHAZADO',
            'observaciones' => 'nullable|string|max:255',
        ]);

        $validated['usuario_actualiza'] = auth()->user()->name ?? 'sistema';

        $solicitud->update($validated);

        return response()->json(['status' => 'success', 'data' => $solicitud], 200);
    }

    // ==========================================
    // DELETE /api/solicitudes/{id}
    // ==========================================
    #[OA\Delete(
        path: '/api/solicitudes/{id}',
        summary: 'Eliminar solicitud',
        description: 'Elimina una solicitud si aún no tiene préstamo aprobado (requiere autenticación).',
        tags: ['Solicitudes'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1))
        ],
        responses: [
            new OA\Response(response: 204, description: 'Solicitud eliminada correctamente'),
            new OA\Response(response: 404, description: 'Solicitud no encontrada'),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function destroy($id)
    {
        $solicitud = Solicitud::find($id);

        if (!$solicitud) {
            return response()->json(['message' => 'Solicitud no encontrada'], 404);
        }

        // 🚨 Aquí podrías verificar si tiene préstamo asociado antes de eliminar
        $solicitud->delete();

        return response()->json(['message' => 'Solicitud eliminada correctamente'], 204);
    }

    #[OA\Put(
        path: '/api/solicitudes/{id}/aprobar',
        summary: 'Aprobar solicitud',
        description: 'Cambia el estado de una solicitud a APROBADO.',
        tags: ['Solicitudes'],
        security: [['bearerAuth' => []]],
        parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'ID de la solicitud a aprobar',
            schema: new OA\Schema(type: 'integer', example: 1)
        )
    ],
        responses: [
        new OA\Response(
            response: 200,
            description: 'Solicitud aprobada correctamente',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'status', type: 'string', example: 'success'),
                    new OA\Property(property: 'message', type: 'string', example: 'Solicitud aprobada correctamente.')
                ]
            )
        ),
        new OA\Response(response: 404, description: 'Solicitud no encontrada'),
        new OA\Response(response: 400, description: 'Solicitud ya estaba aprobada')
    ]
    )]
    public function aprobar($id)
    {
        $solicitud = Solicitud::find($id);

        if (!$solicitud) {
            return response()->json(['status' => 'error', 'message' => 'Solicitud no encontrada.'], 404);
        }

        if ($solicitud->estado === 'APROBADO') {
            return response()->json(['status' => 'error', 'message' => 'La solicitud ya fue aprobada.'], 400);
        }

        $solicitud->update(['estado' => 'APROBADO']);

        return response()->json(['status' => 'success', 'message' => 'Solicitud aprobada correctamente.'], 200);
    }


    #[OA\Put(
        path: '/api/solicitudes/{id}/rechazar',
        summary: 'Rechazar solicitud',
        description: 'Cambia el estado de una solicitud a RECHAZADO.',
        tags: ['Solicitudes'],
        security: [['bearerAuth' => []]],
        parameters: [
        new OA\Parameter(
            name: 'id',
            in: 'path',
            required: true,
            description: 'ID de la solicitud a rechazar',
            schema: new OA\Schema(type: 'integer', example: 1)
        )
    ],
        responses: [
        new OA\Response(
            response: 200,
            description: 'Solicitud rechazada correctamente',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'status', type: 'string', example: 'success'),
                    new OA\Property(property: 'message', type: 'string', example: 'Solicitud rechazada correctamente.')
                ]
            )
        ),
        new OA\Response(response: 404, description: 'Solicitud no encontrada')
    ]
    )]
    public function rechazar($id)
    {
        $solicitud = Solicitud::find($id);

        if (!$solicitud) {
            return response()->json(['status' => 'error', 'message' => 'Solicitud no encontrada.'], 404);
        }

        $solicitud->update(['estado' => 'RECHAZADO']);

        return response()->json(['status' => 'success', 'message' => 'Solicitud rechazada correctamente.'], 200);
    }


    #[OA\Get(
        path: '/api/solicitudes/cliente/{cliente_id}',
        summary: 'Listar solicitudes por cliente',
        description: 'Obtiene todas las solicitudes registradas de un cliente específico.',
        tags: ['Solicitudes'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'cliente_id',
                in: 'path',
                required: true,
                description: 'ID del cliente',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
        new OA\Response(
            response: 200,
            description: 'Listado de solicitudes obtenido correctamente',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'status', type: 'string', example: 'success'),
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(ref: '#/components/schemas/Solicitud')
                    )
                ]
            )
        ),
        new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function solicitudesPorCliente($cliente_id)
    {
        $solicitudes = Solicitud::where('cliente_id', $cliente_id)
            ->with('cliente')
            ->orderBy('id', 'desc')
            ->get();

        if ($solicitudes->isEmpty()) {
            return response()->json(['status' => 'success', 'message' => 'El cliente no tiene solicitudes registradas.']);
        }

        return response()->json([
            'status' => 'success',
            'data' => $solicitudes
        ], 200);
    }

}
