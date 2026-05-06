<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
}
