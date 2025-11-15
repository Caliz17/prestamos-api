<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Clientes', description: 'Gestión de clientes del sistema')]
class ClienteController extends Controller
{
    // ============================
    // GET /api/clientes
    // ============================
    #[OA\Get(
        path: '/api/clientes',
        summary: 'Obtener lista de clientes',
        description: 'Devuelve todos los clientes registrados (requiere autenticación).',
        tags: ['Clientes'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de clientes obtenida correctamente.',
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
            new OA\Response(response: 401, description: 'No autorizado'),
            new OA\Response(response: 500, description: 'Error interno del servidor')
        ]
    )]
    public function index()
    {
        try {
            $totalClientes = Cliente::count();

            // Contar clientes con préstamos (usando la relación)
            $clientesConPrestamos = Cliente::whereHas('solicitudes', function ($query) {
                $query->whereHas('prestamo');
            })->count();

            $clientesNuevosMes = Cliente::whereMonth('created_at', now()->month)->count();

            $clientes = Cliente::with(['solicitudes.prestamo']) // Cargar relaciones
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'total_clientes' => $totalClientes,
                    'clientes_con_prestamos' => $clientesConPrestamos,
                    'clientes_nuevos_mes' => $clientesNuevosMes,
                    'clientes' => $clientes,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener los clientes: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============================
    // POST /api/clientes
    // ============================
    #[OA\Post(
        path: '/api/clientes',
        summary: 'Crear nuevo cliente',
        description: 'Registra un nuevo cliente (requiere autenticación).',
        tags: ['Clientes'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['primer_nombre', 'primer_apellido', 'dpi', 'nit', 'fecha_nacimiento'],
                properties: [
                    new OA\Property(property: 'primer_nombre', type: 'string', example: 'Luis'),
                    new OA\Property(property: 'segundo_nombre', type: 'string', example: 'Fernando'),
                    new OA\Property(property: 'primer_apellido', type: 'string', example: 'Hernández'),
                    new OA\Property(property: 'segundo_apellido', type: 'string', example: 'Gómez'),
                    new OA\Property(property: 'dpi', type: 'string', example: '1234567890101'),
                    new OA\Property(property: 'nit', type: 'string', example: '1234567-8'),
                    new OA\Property(property: 'fecha_nacimiento', type: 'string', format: 'date', example: '1990-05-20'),
                    new OA\Property(property: 'direccion', type: 'string', example: 'Ciudad de Guatemala, Zona 1'),
                    new OA\Property(property: 'correo', type: 'string', format: 'email', example: 'luis@example.com'),
                    new OA\Property(property: 'telefono', type: 'string', example: '5555-1234'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Cliente creado correctamente'),
            new OA\Response(response: 422, description: 'Error de validación'),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'primer_nombre' => 'required|string|max:100',
            'segundo_nombre' => 'nullable|string|max:100',
            'primer_apellido' => 'required|string|max:100',
            'segundo_apellido' => 'nullable|string|max:100',
            'dpi' => 'required|string|unique:clientes,dpi',
            'nit' => 'required|string|unique:clientes,nit',
            'fecha_nacimiento' => 'required|date',
            'correo' => 'nullable|email',
            'telefono' => 'nullable|string',
            'direccion' => 'nullable|string',
        ]);

        Cliente::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Cliente creado correctamente.'
        ], 201);
    }

    // ============================
    // GET /api/clientes/{id}
    // ============================
    #[OA\Get(
        path: '/api/clientes/{id}',
        summary: 'Mostrar cliente específico',
        description: 'Obtiene los datos de un cliente por su identificador (requiere autenticación).',
        tags: ['Clientes'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'ID del cliente a recuperar',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Cliente encontrado'),
            new OA\Response(response: 404, description: 'Cliente no encontrado'),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function show($id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json(['message' => 'Cliente no encontrado'], 404);
        }

        return response()->json(['status' => 'success', 'data' => $cliente], 200);
    }

    // ============================
    // PUT /api/clientes/{id}
    // ============================
    #[OA\Put(
        path: '/api/clientes/{id}',
        summary: 'Actualizar cliente',
        description: 'Modifica los datos de un cliente existente (requiere autenticación).',
        tags: ['Clientes'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Cliente actualizado correctamente'),
            new OA\Response(response: 404, description: 'Cliente no encontrado'),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function update(Request $request, $id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json(['status' => 'error', 'message' => 'Cliente no encontrado.'], 404);
        }

        $cliente->update($request->all());

        return response()->json(['status' => 'success', 'message' => 'Cliente actualizado correctamente.'], 200);
    }

    // ============================
    // DELETE /api/clientes/{id}
    // ============================
    #[OA\Delete(
        path: '/api/clientes/{id}',
        summary: 'Eliminar cliente',
        description: 'Elimina un cliente existente y sus datos relacionados (requiere autenticación).',
        tags: ['Clientes'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1))
        ],
        responses: [
            new OA\Response(response: 204, description: 'Cliente eliminado correctamente'),
            new OA\Response(response: 404, description: 'Cliente no encontrado'),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function destroy($id)
    {
        $cliente = Cliente::find($id);

        if (!$cliente) {
            return response()->json(['status' => 'error', 'message' => 'Cliente no encontrado.'], 404);
        }

        $cliente->delete();

        return response()->json(['status' => 'success', 'message' => 'Cliente eliminado correctamente.'], 200);
    }

}
