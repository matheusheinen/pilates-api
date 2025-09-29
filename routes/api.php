<?php

use App\Http\Controllers\Api\AulaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\AvaliacaoPosturalController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InscricaoController;

// --- Rotas Públicas ---
// Estas rotas não requerem autenticação.
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [UsuarioController::class, 'store']);

// --- Rotas Protegidas ---
// Todas as rotas abaixo deste ponto exigirão um token válido automaticamente.
Route::middleware('auth:sanctum')->group(function () {
    // Rota para obter os dados do usuário logado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    Route::post('/logout', [AuthController::class, 'logout']);

    // Recursos Principais (CRUDs)
    Route::apiResource('usuarios', UsuarioController::class)->except(['store']); 
    Route::apiResource('avaliacoes-posturais', AvaliacaoPosturalController::class);
    Route::apiResource('inscricoes', InscricaoController::class);
    Route::apiResource('aulas', AulaController::class);
});

