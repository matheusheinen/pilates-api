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
     * Lida com uma tentativa de autenticação usando email OU celular, seguindo o padrão manual de checagem.
     */
    public function login(Request $request)
    {
        // 1. Validação genérica para 'login' e 'senha'
        $request->validate([
            'login' => 'required|string',
            'senha' => 'required',
        ]);

        $loginIdentifier = $request->login;

        // 2. Determina o campo de busca (email ou celular)
        $fieldType = 'email';
        $searchValue = $loginIdentifier;

        // Remove máscara do celular (se houver)
        $cleanIdentifier = preg_replace('/\D/', '', $loginIdentifier);

        // Se contém '@', é tratado como email. Caso contrário, e se o comprimento for de telefone, assume celular.
        if (!filter_var($loginIdentifier, FILTER_VALIDATE_EMAIL) && strlen($cleanIdentifier) >= 10 && strlen($cleanIdentifier) <= 20) {
            $fieldType = 'celular';
            $searchValue = $cleanIdentifier;
        }

        // 3. Busca o usuário no campo determinado
        $usuario = Usuario::where($fieldType, $searchValue)->first();

        // 4. Checagem manual de usuário e senha (Seguindo o padrão original)
        if (! $usuario || ! Hash::check($request->senha, $usuario->senha)) {
            // Usa a exceção para retornar 401 e a mensagem de erro
            throw ValidationException::withMessages([
                'login' => ['As credenciais fornecidas estão incorretas.'],
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
