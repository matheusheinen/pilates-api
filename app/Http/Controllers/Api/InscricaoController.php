<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInscricaoRequest;
use App\Http\Requests\UpdateInscricaoRequest;
use App\Models\Inscricao;
use App\Models\HorarioAgenda;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class InscricaoController extends Controller
{
    /**
     * Lista todas as inscrições.
     */
    public function index(): JsonResponse
    {
        $inscricoes = Inscricao::with(['usuario', 'plano', 'horariosAgenda'])->get();
        return response()->json($inscricoes);
    }

    /**
     * Cria uma nova inscrição e aloca os horários fixos para o aluno.
     */
    public function store(StoreInscricaoRequest $request): JsonResponse
    {
        $dadosValidados = $request->validated();

        // IDs dos horários que o aluno escolheu (ex: [1, 5, 12])
        $horariosIds = $dadosValidados['horarios_agenda_ids'];

        // 1. Verifica se todos os horários escolhidos estão realmente livres
        $horariosOcupados = HorarioAgenda::whereIn('id', $horariosIds)->whereNotNull('inscricao_id')->count();

        if ($horariosOcupados > 0) {
            return response()->json(['message' => 'Um ou mais dos horários escolhidos já estão ocupados.'], 409); // Conflict
        }

        // 2. Usa uma transação para garantir a integridade dos dados
        DB::beginTransaction();
        try {
            // Cria a inscrição
            $inscricao = Inscricao::create([
                'usuario_id' => $dadosValidados['usuario_id'],
                'plano_id' => $dadosValidados['plano_id'],
                'data_inicio' => $dadosValidados['data_inicio'],
                'status' => 'ativa',
            ]);

            // Aloca os horários para esta nova inscrição
            HorarioAgenda::whereIn('id', $horariosIds)->update(['inscricao_id' => $inscricao->id]);

            DB::commit(); // Confirma as alterações

            // Retorna a inscrição completa com os horários associados
            return response()->json($inscricao->load('horariosAgenda'), 201);

        } catch (\Exception $e) {
            DB::rollBack(); // Desfaz tudo se algo der errado
            return response()->json(['message' => 'Ocorreu um erro ao criar a inscrição.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mostra os detalhes de uma inscrição específica.
     */
    public function show(Inscricao $inscricao): JsonResponse
    {
        return response()->json($inscricao->load(['usuario', 'plano', 'horariosAgenda']));
    }

    /**
     * Atualiza uma inscrição.
     */
    public function update(UpdateInscricaoRequest $request, Inscricao $inscricao): JsonResponse
    {
        // Esta lógica pode ser expandida no futuro para permitir a troca de horários fixos
        $inscricao->update($request->validated());
        return response()->json($inscricao->load('horariosAgenda'));
    }

    /**
     * "Apaga" uma inscrição (geralmente inativando-a).
     */
    public function destroy(Inscricao $inscricao): JsonResponse
    {
        // Libera os horários fixos que estavam associados a esta inscrição
        HorarioAgenda::where('inscricao_id', $inscricao->id)->update(['inscricao_id' => null]);

        // Inativa ou apaga a inscrição
        $inscricao->update(['status' => 'inativa']); // ou $inscricao->delete();

        return response()->json(null, 204);
    }
}
