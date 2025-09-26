<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\AvaliacaoPosturalController;
use App\Http\Controllers\Api\AuthController;

Route::apiResource('usuarios', UsuarioController::class);
Route::apiResource('avaliacoes-posturais', AvaliacaoPosturalController::class);

Route::post('/login', [AuthController::class, 'login']);