<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Prestamo;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Illuminate\Support\Facades\DB;

#[OA\Tag(name: 'Pagos', description: 'Gestión de pagos de préstamos')]
class PagoController extends Controller
{
    #[OA\Get(
        path: '/api/pagos',
        summary: 'Listar todos los pagos',
        description: 'Obtiene todos los registros de pagos (requiere autenticación).',
        tags: ['Pagos'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Listado de pagos obtenido correctamente'),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function index()
    {
        $pagos = Pago::with('prestamo.solicitud.cliente')->latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $pagos,
        ], 200);
    }

    #[OA\Post(
        path: '/api/pagos',
        summary: 'Registrar nuevo pago',
        description: 'Registra un pago asociado a un préstamo y actualiza automáticamente su saldo.',
        tags: ['Pagos'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['prestamo_id', 'monto_pagado', 'metodo_pago'],
                properties: [
                    new OA\Property(property: 'prestamo_id', type: 'integer', example: 2),
                    new OA\Property(property: 'monto_pagado', type: 'number', format: 'float', example: 1000.00),
                    new OA\Property(property: 'metodo_pago', type: 'string', example: 'EFECTIVO'),
                    new OA\Property(property: 'observaciones', type: 'string', example: 'Cobro en zona 5, cliente moroso.'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Pago registrado correctamente'),
            new OA\Response(response: 404, description: 'Préstamo no encontrado'),
            new OA\Response(response: 400, description: 'Pago excede el saldo actual')
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'prestamo_id' => 'required|exists:prestamos,id',
            'monto_pagado' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|string|in:EFECTIVO,TARJETA,TRANSFERENCIA',
            'observaciones' => 'nullable|string',
        ]);

        $prestamo = Prestamo::find($validated['prestamo_id']);

        if ($validated['monto_pagado'] > $prestamo->saldo_actual) {
            return response()->json([
                'status' => 'error',
                'message' => 'El monto pagado no puede exceder el saldo actual del préstamo.'
            ], 400);
        }

        DB::transaction(function () use ($prestamo, $validated) {
            $pago = Pago::create([
                'prestamo_id' => $prestamo->id,
                'fecha_pago' => now(),
                'monto_pagado' => $validated['monto_pagado'],
                'metodo_pago' => $validated['metodo_pago'],
                'observaciones' => $validated['observaciones'] ?? null,
            ]);

            // actualizar saldo
            $nuevoSaldo = $prestamo->saldo_actual - $validated['monto_pagado'];
            $prestamo->update([
                'saldo_actual' => $nuevoSaldo,
                'estado' => $nuevoSaldo <= 0 ? 'PAGADO' : $prestamo->estado,
            ]);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Pago registrado y saldo actualizado correctamente.'
        ], 201);
    }

    #[OA\Get(
        path: '/api/pagos/{id}',
        summary: 'Ver detalle de pago',
        description: 'Obtiene los detalles de un pago específico.',
        tags: ['Pagos'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Pago encontrado'),
            new OA\Response(response: 404, description: 'Pago no encontrado')
        ]
    )]
    public function show($id)
    {
        $pago = Pago::with('prestamo.solicitud.cliente')->find($id);

        if (!$pago) {
            return response()->json(['message' => 'Pago no encontrado'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $pago,
        ], 200);
    }

    #[OA\Delete(
        path: '/api/pagos/{id}',
        summary: 'Eliminar pago',
        description: 'Elimina un pago y restaura el saldo del préstamo.',
        tags: ['Pagos'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer', example: 1))
        ],
        responses: [
            new OA\Response(response: 204, description: 'Pago eliminado correctamente'),
            new OA\Response(response: 404, description: 'Pago no encontrado')
        ]
    )]
    public function destroy($id)
    {
        $pago = Pago::find($id);

        if (!$pago) {
            return response()->json(['message' => 'Pago no encontrado'], 404);
        }

        DB::transaction(function () use ($pago) {
            $prestamo = $pago->prestamo;
            $prestamo->update([
                'saldo_actual' => $prestamo->saldo_actual + $pago->monto_pagado,
                'estado' => 'ACTIVO',
            ]);

            $pago->delete();
        });

        return response()->json(['message' => 'Pago eliminado y saldo restaurado correctamente.'], 204);
    }
}
