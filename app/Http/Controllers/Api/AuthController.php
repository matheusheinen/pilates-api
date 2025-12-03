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
        // 1. Validação genérica
        $request->validate([
            'login' => 'required|string',
            'senha' => 'required',
        ]);

        $loginIdentifier = $request->input('login');

        // 2. Determina o campo de busca (email ou cpf)

        // Remove tudo que não é dígito (pontos e traços do CPF)
        $cleanIdentifier = preg_replace('/\D/', '', $loginIdentifier);

        // Lógica de decisão:
        // Se for um formato de email válido, busca por email.
        // Caso contrário, assume que é um CPF (usando apenas os números).
        if (filter_var($loginIdentifier, FILTER_VALIDATE_EMAIL)) {
            $fieldType = 'email';
            $searchValue = $loginIdentifier;
        } else {
            $fieldType = 'cpf';
            $searchValue = $cleanIdentifier;
        }

        // 3. Busca o usuário no campo determinado
        $usuario = Usuario::where($fieldType, $searchValue)->first();

        // 4. Checagem manual de usuário e senha
        if (! $usuario || ! Hash::check($request->senha, $usuario->senha)) {
            throw ValidationException::withMessages([
                'login' => ['Credenciais incorretas (Verifique E-mail/CPF e Senha).'],
            ])->status(401);
        }

        // 5. Criação do Token
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
