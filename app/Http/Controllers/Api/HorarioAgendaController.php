<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HorarioAgenda;
use App\Models\Aula;
use Carbon\Carbon;
use App\Models\HorarioAluno;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class HorarioAgendaController extends Controller
{
    public function index()
    {
        try {
            // 1. A consulta busca apenas os horários ATIVOS (cadastrados pela professora)
            $horarios = HorarioAgenda::where('status', 'ativo')

                // 2. Com o withCount, incluímos o campo 'ocupacao' (essencial para Matrícula.vue)
                // O Vue ignora essa informação se for só para listagem simples.
                ->withCount(['horariosAluno as ocupacao' => function ($query) {
                    // Contamos apenas os vínculos ativos para determinar a vaga real
                    $query->where('status', 'ativo');
                }])

                // 3. Ordenamos para a visualização correta na tela
                ->orderBy('dia_semana')
                ->orderBy('horario_inicio')
                ->get();

            // Retorna os dados no formato Resource Collection (com 'data')
            return response()->json(['data' => $horarios]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro interno do servidor ao carregar a agenda.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ESTE É O MÉTODO QUE ESTAVA DANDO ERRO
     * Agora ele busca pela contagem de vagas, não por inscricao_id
     */
    public function disponiveis(): JsonResponse
    {
        $horarios = HorarioAgenda::where('status', 'ativo')
            ->withCount('inscricoes')
            ->orderBy('dia_semana')
            ->orderBy('horario_inicio')
            ->get()
            ->filter(function ($horario) {
                // Usa o campo 'vagas_totais' do banco. Se for null, assume 3.
                $limite = $horario->vagas_totais ?? 3;
                // Só retorna se tiver vaga (inscritos < limite)
                return $horario->inscricoes_count < $limite;
            })
            ->values(); // Reorganiza os índices do array

        return response()->json($horarios);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'dia_semana' => 'required',
            'horario_inicio' => 'required',
            'duracao_minutos' => 'required|integer',
            'vagas_totais' => 'required|integer|min:1', // Valida o novo campo
        ]);

        $horario = HorarioAgenda::create($validated);
        return response()->json($horario, 201);
    }

    public function destroy($id): JsonResponse
    {
        $horario = HorarioAgenda::withCount('inscricoes')->findOrFail($id);

        if ($horario->inscricoes_count > 0) {
            return response()->json(['message' => 'Não é possível excluir horário com alunos matriculados.'], 409);
        }

        $horario->delete();
        return response()->json(null, 204);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $horarioAgenda = HorarioAgenda::findOrFail($id);

        // Adicione a validação (idealmente em um Request, mas aqui direto para simplificar)
        $validated = $request->validate([
            'dia_semana' => 'required',
            'horario_inicio' => 'required',
            'duracao_minutos' => 'required|integer',
            'vagas_totais' => 'required|integer|min:1',
            'status' => 'required|string', // Adicionei status
        ]);

        // Checa se os campos que afetam a aula mudaram
        $horarioMudou = $horarioAgenda->horario_inicio != $validated['horario_inicio'] ||
                        $horarioAgenda->duracao_minutos != $validated['duracao_minutos'];

        // 1. Atualiza o HorarioAgenda
        $horarioAgenda->update($validated);

        // 2. Sincroniza as Aulas futuras
        if ($horarioMudou) {
            $dataAtual = Carbon::now();

            // Encontra todas as Aulas futuras vinculadas a este HorarioAgenda e atualiza
            Aula::where('horario_agenda_id', $horarioAgenda->id)
                ->where('data_hora_inicio', '>', $dataAtual)
                ->get()
                ->each(function ($aula) use ($validated) {
                    // a. Recalcula a data/hora de início
                    $dataAntiga = Carbon::parse($aula->data_hora_inicio)->format('Y-m-d');
                    $novaDataHoraInicio = Carbon::parse($dataAntiga . ' ' . $validated['horario_inicio']);

                    // b. Atualiza a Aula
                    $aula->update([
                        'data_hora_inicio' => $novaDataHoraInicio,
                        'duracao_minutos' => $validated['duracao_minutos'],
                    ]);
                });
        }

        // 3. (Opcional) Se o horário foi inativado, cancelar futuras aulas.
        if ($validated['status'] === 'inativo') {
            Aula::where('horario_agenda_id', $horarioAgenda->id)
                ->where('data_hora_inicio', '>', Carbon::now())
                ->update(['status' => 'cancelada']);
        }

        return response()->json($horarioAgenda, 200);
    }
}
