<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Enums\Rol;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'API Usuarios',
    version: '1.0.0',
    description: 'API de autenticación y gestión de usuarios'
)]
#[OA\Server(
    url: 'http://localhost:5501/api/v1',
    description: 'Servidor local'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Introduce el token Bearer obtenido al hacer login'
)]
class AuthController extends Controller
{
    #[OA\Post(
        path: '/registrar',
        summary: 'Registrar un nuevo usuario',
        tags: ['Autenticación'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Juan García'),
                    new OA\Property(property: 'email', type: 'string', example: 'juan@email.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'MiPassword123'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Usuario registrado con éxito',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Usuario registrado con éxito'),
                        new OA\Property(
                            property: 'user',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'name', type: 'string', example: 'Juan García'),
                                new OA\Property(property: 'email', type: 'string', example: 'juan@email.com'),
                                new OA\Property(property: 'rol', type: 'string', example: 'Cliente'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Datos inválidos o email ya en uso'),
        ]
    )]
    public function registrar(Request $request)
    {
       $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol' => Rol::CLIENTE,
        ]);

        return response()->json(['message' => 'Usuario registrado con éxito', 'user' => $user], 201);
    }

    #[OA\Post(
        path: '/login',
        summary: 'Iniciar sesión',
        tags: ['Autenticación'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'admin@habita.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'Admin1234'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login exitoso',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'access_token', type: 'string', example: '1|abc123...'),
                        new OA\Property(property: 'token_type', type: 'string', example: 'Bearer'),
                        new OA\Property(property: 'rol', type: 'string', example: 'Administrador'),
                        new OA\Property(property: 'abilities', type: 'array', items: new OA\Items(type: 'string', example: 'muebles.crear')),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Credenciales incorrectas'),
        ]
    )]
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        // abilities
        $abilities = match($user->rol) {
            Rol::ADMINISTRADOR => ['perfil.ver', 'usuarios.ver', 'usuarios.crear', 'usuarios.editar', 'usuarios.eliminar', 'muebles.ver', 'muebles.crear', 'muebles.editar', 'muebles.eliminar', 'admin.panel'],
            Rol::GESTOR => ['perfil.ver', 'muebles.ver', 'muebles.crear', 'muebles.editar', 'muebles.eliminar'],
            Rol::CLIENTE => ['perfil.ver', 'muebles.ver', 'carrito.gestionar', 'pedidos.crear'],
        };

        $token = $user->createToken('auth_token', $abilities)->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'rol' => $user->rol,
            'abilities' => $abilities
        ]);
    }

    #[OA\Get(
        path: '/perfil',
        summary: 'Ver perfil del usuario autenticado',
        tags: ['Autenticación'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Datos del usuario',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'name', type: 'string', example: 'Juan García'),
                        new OA\Property(property: 'email', type: 'string', example: 'juan@email.com'),
                        new OA\Property(property: 'rol', type: 'string', example: 'Cliente'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'No autenticado'),
        ]
    )]
    public function perfil(Request $request)
    {
        if (!$request->user()->tokenCan('perfil.ver')) {
            return response()->json(['message' => 'No tienes permisos para ver el perfil'], 403);
        }

        return response()->json($request->user());
    }

    #[OA\Post(
        path: '/logout',
        summary: 'Cerrar sesión',
        tags: ['Autenticación'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Sesión cerrada correctamente'),
            new OA\Response(response: 401, description: 'No autenticado'),
        ]
    )]
    public function logout(Request $request)
    {
        if (!$request->user()->tokenCan('perfil.ver')) {
            return response()->json(['message' => 'Acceso denegado'], 403);
        }

        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada correctamente y token eliminado']);
    }
}
