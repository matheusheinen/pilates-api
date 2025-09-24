<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\AvaliacaoPosturalController;

Route::apiResource('usuarios', UsuarioController::class);
Route::apiResource('avaliacoes-posturais', AvaliacaoPosturalController::class);