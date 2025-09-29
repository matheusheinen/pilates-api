<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Lida com uma tentativa de autenticação.
     */
    public function login(Request $request)
    {

        $request->validate([
            'email' => 'required|email',
            'senha' => 'required',
        ]);


        $usuario = Usuario::where('email', $request->email)->first();


        if (! $usuario || ! Hash::check($request->senha, $usuario->senha)) {
            return response()->json([
                'message' => 'As credenciais fornecidas estão incorretas.'
            ], 401);
        }

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'usuario' => $usuario
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }
}
