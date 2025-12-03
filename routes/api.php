<?php

use App\Http\Controllers\Api\AulaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UsuarioController;
use App\Http\Controllers\Api\AvaliacaoPosturalController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InscricaoController;
use App\Http\Controllers\Api\HorarioAgendaController;
use App\Http\Controllers\Api\PlanoController;
use App\Http\Controllers\Api\MensalidadeController;

// --- Rotas Públicas ---
// Estas rotas não requerem autenticação.
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [UsuarioController::class, 'store']);
Route::get('/aulas/disponiveis-publico', [AulaController::class, 'disponiveisPublico']);

// --- Rotas Protegidas ---
// Todas as rotas abaixo deste ponto exigirão um token válido automaticamente.
Route::middleware('auth:sanctum')->group(function () {
    // Rota para obter os dados do usuário logado
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/usuarios/{id}/avaliacoes', [UsuarioController::class, 'avaliacoesPosturais']);

    Route::get('/aulas/disponiveis', [AulaController::class, 'listarDisponiveis']);
    Route::get('/aulas/calendario', [AulaController::class, 'listagemCalendario']);
    Route::get('/aulas/disponiveis-reagendamento', [AulaController::class, 'disponiveisParaReagendamento']);
    Route::post('/aulas/{id}/reagendar', [AulaController::class, 'reagendar']);
    Route::post('/aulas/cancelar-turma', [AulaController::class, 'cancelarTurma']);
    Route::post('/aulas/reagendar-turma', [AulaController::class, 'reagendarTurma']);
    Route::post('/aulas/{id}/cancelar', [AulaController::class, 'cancelar']);

    Route::post('/agenda/atualizar', [AulaController::class, 'atualizarAgenda']);

    Route::get('/mensalidades', [MensalidadeController::class, 'index']);
    Route::post('/mensalidades', [MensalidadeController::class, 'store']); // Criar avulsa
    Route::post('/mensalidades/gerar-massivo', [MensalidadeController::class, 'gerarMassivo']); // Gerar do mês
    Route::post('/mensalidades/{id}/pagar', [MensalidadeController::class, 'registrarPagamento']);
    Route::post('/mensalidades/{id}/comprovante', [MensalidadeController::class, 'enviarComprovante']);
    Route::get('/mensalidades/{id}/comprovante', [MensalidadeController::class, 'visualizarComprovante']);
    Route::post('/mensalidades/{id}/aprovar', [MensalidadeController::class, 'aprovarPagamento']);
    Route::post('/mensalidades/{id}/rejeitar', [MensalidadeController::class, 'rejeitarPagamento']);

    Route::get('/avaliacoes-posturais/{id}/visualizar-anexo', [AvaliacaoPosturalController::class, 'visualizarAnexo']);

    // Recursos Principais (CRUDs)
    Route::apiResource('usuarios', UsuarioController::class);
    Route::apiResource('avaliacoes-posturais', AvaliacaoPosturalController::class);
    Route::apiResource('inscricoes', InscricaoController::class);
    Route::apiResource('aulas', AulaController::class);
    Route::apiResource('horarios-agenda', HorarioAgendaController::class);
    Route::apiResource('planos', PlanoController::class);

});

