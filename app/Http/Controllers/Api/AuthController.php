<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;
use Illuminate\Support\Facades\Password;

#[OA\Tag(name: 'Auth', description: 'Autenticación de usuarios')]
class AuthController extends Controller
{
    #[OA\Post(
        path: '/api/register',
        summary: 'Registro de usuario',
        description: 'Registra un nuevo usuario y devuelve un token de autenticación.',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Juan Pérez'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'juan@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'secret123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'secret123'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Usuario registrado correctamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'user', type: 'object'),
                        new OA\Property(property: 'token', type: 'string', example: '1|abcd1234tokenexample'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Error de validación')
        ]
    )]
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    #[OA\Post(
        path: '/api/login',
        summary: 'Iniciar sesión',
        description: 'Autentica un usuario y devuelve un token de acceso.',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'juan@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'secret123'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Inicio de sesión exitoso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'access_token', type: 'string', example: '1|token123456789'),
                        new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Credenciales incorrectas')
        ]
    )]
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales no son correctas.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    #[OA\Post(
        path: '/api/logout',
        summary: 'Cerrar sesión',
        description: 'Revoca el token de autenticación actual.',
        tags: ['Auth'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sesión cerrada correctamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Sesión cerrada correctamente.')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Token inválido o expirado')
        ]
    )]
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente.'
        ]);
    }

    #[OA\Get(
        path: '/api/me',
        summary: 'Perfil del usuario autenticado',
        description: 'Devuelve la información del usuario actualmente autenticado.',
        tags: ['Auth'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Datos del usuario autenticado',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Juan Pérez'),
                        new OA\Property(property: 'email', type: 'string', example: 'juan@example.com'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Token inválido o no proporcionado')
        ]
    )]
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    #[OA\Post(
        path: '/api/forgot-password',
        summary: 'Solicitar restablecimiento de contraseña',
        description: 'Envía un enlace de restablecimiento de contraseña al correo del usuario.',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'juan@example.com'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Correo enviado correctamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Se ha enviado el enlace de restablecimiento.')
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Error de validación')
        ]
    )]
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => 'No se pudo enviar el enlace a ese correo.'
            ]);
        }

        return response()->json([
            'message' => 'Se ha enviado el enlace de restablecimiento.'
        ]);
    }

    #[OA\Post(
        path: '/api/reset-password',
        summary: 'Restablecer contraseña',
        description: 'Restablece la contraseña usando un token previamente enviado.',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'token', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'juan@example.com'),
                    new OA\Property(property: 'token', type: 'string', example: 'abc123'),
                    new OA\Property(property: 'password', type: 'string', example: 'newpassword123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', example: 'newpassword123'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Contraseña restablecida correctamente',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Contraseña actualizada correctamente.')
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Token inválido o datos incorrectos')
        ]
    )]
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => 'La información proporcionada no es válida o el token ha expirado.'
            ]);
        }

        return response()->json([
            'message' => 'Contraseña actualizada correctamente.'
        ]);
    }

}
