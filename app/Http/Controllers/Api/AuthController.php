<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; // Importa a classe base Controller
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Import necessário
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function login(Request $request)
    {
        $credenciais = $request->validate([
            'email' => 'required|email',
            'senha' => 'required',
        ]);


        if (Auth::attempt($credenciais)) {

            $usuario = Usuario::where('email', $credenciais['email'])->first();

            $token = $usuario->createToken('auth_token')->plainTextToken;


            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'usuario' => $usuario,
            ]);
        }
        return response()->json([
            'message' => 'As credenciais fornecidas estão incorretas.'
        ], 401);
    }
}
