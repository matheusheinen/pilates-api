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
     * Lida com uma tentativa de autenticação usando email OU CPF.
     */
    public function login(Request $request)
    {

        $request->validate([
            'login' => 'required|string',
            'senha' => 'required',
        ]);

        $loginIdentifier = $request->input('login');

        $cleanIdentifier = preg_replace('/\D/', '', $loginIdentifier);

        if (filter_var($loginIdentifier, FILTER_VALIDATE_EMAIL)) {
            $fieldType = 'email';
            $searchValue = $loginIdentifier;
        } else {
            $fieldType = 'cpf';
            $searchValue = $cleanIdentifier;
        }

        $usuario = Usuario::where($fieldType, $searchValue)->first();

        if (! $usuario || ! Hash::check($request->senha, $usuario->senha)) {
            throw ValidationException::withMessages([
                'login' => ['Credenciais incorretas (Verifique E-mail/CPF e Senha).'],
            ])->status(401);
        }

        $token = $usuario->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'usuario' => $usuario,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }
}
