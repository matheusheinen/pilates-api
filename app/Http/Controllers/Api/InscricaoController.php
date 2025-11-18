<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInscricaoRequest;
use App\Models\Inscricao;
use App\Models\HorarioAgenda;
use App\Models\Plano;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class InscricaoController extends Controller
{
    /**
     * Lista todas as inscrições com seus relacionamentos.
     */
    public function index(): JsonResponse
    {
        $inscricoes = Inscricao::with(['usuario', 'plano', 'horarios'])->get();
        return response()->json($inscricoes);
    }

    /**
     * Cria a matrícula validando plano e vagas.
     */
    public function store(StoreInscricaoRequest $request): JsonResponse
    {
        $dados = $request->validated();
        $horariosIds = $dados['horarios_agenda_ids'];

        // 1. Validar Quantidade de Aulas do Plano
        $plano = Plano::findOrFail($dados['plano_id']);

        if (count($horariosIds) !== $plano->numero_aulas) {
            return response()->json([
                'message' => "O plano '{$plano->nome}' exige exatamente {$plano->numero_aulas} horários selecionados.",
                'selecionados' => count($horariosIds)
            ], 422);
        }

        // 2. Verificar Disponibilidade de Vagas (Bloqueio de Concorrência)
        // Carregamos os horários e contamos quantos alunos JÁ estão neles
        foreach ($horariosIds as $id) {
            $horario = HorarioAgenda::withCount('inscricoes')->find($id);

            // Se inscritos >= vagas totais, bloqueia
            if ($horario->inscricoes_count >= $horario->vagas_totais) {
                return response()->json([
                    'message' => "O horário de {$this->formatarDia($horario->dia_semana)} às {$this->formatarHora($horario->horario_inicio)} acabou de atingir o limite de {$horario->vagas_totais} alunos."
                ], 409); // Conflict
            }
        }

        // 3. Realizar a Matrícula (Transação)
        DB::beginTransaction();
        try {
            // Cria o registro da inscrição
            $inscricao = Inscricao::create([
                'usuario_id' => $dados['usuario_id'],
                'plano_id'   => $dados['plano_id'],
                'data_inicio'=> $dados['data_inicio'],
                'status'     => 'ativa',
            ]);

            // Vincula os horários na tabela pivô (horario_inscricao)
            $inscricao->horarios()->attach($horariosIds);

            DB::commit();

            return response()->json($inscricao->load(['horarios', 'plano']), 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro ao realizar inscrição.', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(Inscricao $inscricao): JsonResponse
    {
        return response()->json($inscricao->load(['usuario', 'plano', 'horarios']));
    }

    public function destroy(Inscricao $inscricao): JsonResponse
    {
        // Remove os vínculos da tabela pivô automaticamente devido ao cascade no banco,
        // mas podemos forçar o detach para garantir
        $inscricao->horarios()->detach();
        $inscricao->delete();

        return response()->json(null, 204);
    }

    // Auxiliares para mensagem de erro bonita
    private function formatarDia($dia) {
        $dias = [1=>'Segunda', 2=>'Terça', 3=>'Quarta', 4=>'Quinta', 5=>'Sexta', 6=>'Sábado', 7=>'Domingo'];
        return $dias[$dia] ?? 'Dia inválido';
    }

    private function formatarHora($hora) {
        return substr($hora, 0, 5);
    }
}
