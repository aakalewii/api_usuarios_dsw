<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Enums\Rol;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
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

    public function perfil(Request $request)
    {
        // Comprobación manual de ability
        if (!$request->user()->tokenCan('perfil.ver')) {
            return response()->json(['message' => 'No tienes permisos para ver el perfil'], 403);
        }

        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        // Comprobación manual de ability
        if (!$request->user()->tokenCan('perfil.ver')) {
            return response()->json(['message' => 'Acceso denegado'], 403);
        }

        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada correctamente y token eliminado']);
    }

}
