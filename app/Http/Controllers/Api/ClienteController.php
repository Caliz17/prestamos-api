<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ClienteController extends Controller
{

    #[OA\Get(
        path: '/api/clientes',
        summary: 'Obtener lista de clientes',
        description: 'Devuelve todos los clientes registrados',
        tags: ['Clientes'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Operación exitosa',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Cliente')
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Error del servidor'
            )
        ]
    )]
    public function index()
    {
        try {
            $clientes = Cliente::all();
            return response()->json([
                'status' => 'success',
                'data' => $clientes,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'error' => 'Error al obtener los clientes',
                'message' => 'Error al obtener los clientes: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    #[OA\Get(
        path: '/api/clientes/{id}',
        operationId: 'showCliente',
        tags: ['Clientes'],
        summary: 'Mostrar cliente específico',
        description: 'Obtiene los datos detallados de un cliente por su identificador.',
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID del cliente a recuperar',
                schema: new OA\Schema(type: 'integer', format: 'int64', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Operación exitosa',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/Cliente'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 404,
                description: 'Cliente no encontrado',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Cliente no encontrado')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'No autorizado',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'No autorizado')
                    ]
                )
            )
        ],
        security: [['bearerAuth' => []]]
    )]
    public function show(Cliente $cliente)
    {
        return response()->json([
            'success' => true,
            'data' => $cliente
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cliente $cliente)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente)
    {
        //
    }
}
