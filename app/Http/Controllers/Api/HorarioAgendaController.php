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
            $horarios = HorarioAgenda::withCount(['horariosAluno as ocupacao' => function ($query) {
                    $query->where('status', 'ativo');
                }])
                ->orderBy('dia_semana')
                ->orderBy('horario_inicio')
                ->get();

            return response()->json(['data' => $horarios]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao carregar a agenda.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        return response()->json(HorarioAgenda::findOrFail($id));
    }

    public function store(Request $request)
    {
        // Validação básica dos campos
        $request->validate([
            'dia_semana' => 'required|integer|between:1,7',
            'horario_inicio' => 'required', // formato H:i
            'duracao_minutos' => 'required|integer|min:10',
            'vagas_totais' => 'required|integer|min:1',
        ]);

        // --- VALIDAÇÃO DE CONFLITO DE HORÁRIO ---
        $conflito = $this->verificarConflito(
            $request->dia_semana,
            $request->horario_inicio,
            $request->duracao_minutos
        );

        if ($conflito) {
            return response()->json([
                'message' => 'Choque de horário! Já existe um horário cadastrado neste intervalo de tempo.'
            ], 422);
        }
        // ----------------------------------------

        $horario = HorarioAgenda::create($request->all());

        return response()->json($horario, 201);
    }

    public function update(Request $request, $id)
    {
        $horarioAgenda = HorarioAgenda::findOrFail($id);

        $request->validate([
            'dia_semana' => 'sometimes|integer|between:1,7',
            'horario_inicio' => 'sometimes',
            'duracao_minutos' => 'sometimes|integer|min:10',
        ]);

        // Dados para verificação (usa o novo se enviado, ou mantém o antigo)
        $dia = $request->dia_semana ?? $horarioAgenda->dia_semana;
        $inicio = $request->horario_inicio ?? $horarioAgenda->horario_inicio;
        $duracao = $request->duracao_minutos ?? $horarioAgenda->duracao_minutos;

        // --- VALIDAÇÃO DE CONFLITO DE HORÁRIO ---
        // Passamos o ID atual para ignorar ele mesmo na verificação
        $conflito = $this->verificarConflito($dia, $inicio, $duracao, $id);

        if ($conflito) {
            return response()->json([
                'message' => 'Choque de horário! A alteração conflita com outro horário existente.'
            ], 422);
        }
        // ----------------------------------------

        $horarioMudou = $horarioAgenda->horario_inicio != $inicio ||
                       $horarioAgenda->duracao_minutos != $duracao;

        // Atualiza
        $horarioAgenda->update($request->all());

        // Sincroniza Aulas Futuras se o horário mudou
        if ($horarioMudou) {
            $this->sincronizarAulasFuturas($horarioAgenda, $inicio, $duracao);
        }

        return response()->json($horarioAgenda);
    }

    public function destroy($id)
    {
        try {
            $horario = HorarioAgenda::withCount('horariosAluno')->findOrFail($id);

            if ($horario->horarios_aluno_count > 0) {
                return response()->json(['message' => 'Não é possível excluir. Existem alunos matriculados neste horário.'], 422);
            }

            $horario->delete();
            return response()->json(['message' => 'Horário excluído com sucesso.']);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao excluir: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Verifica se o novo horário colide com algum existente.
     * Retorna TRUE se houver conflito.
     */
    private function verificarConflito($diaSemana, $horaInicio, $duracao, $ignorarId = null)
    {
        // 1. Calcula início e fim do NOVO horário
        // Usamos uma data arbitrária (hoje) apenas para poder somar os minutos corretamente
        $novoInicio = Carbon::parse($horaInicio);
        $novoFim = $novoInicio->copy()->addMinutes($duracao);

        // 2. Busca todos os horários do MESMO DIA
        $query = HorarioAgenda::where('dia_semana', $diaSemana)
            ->where('status', 'ativo'); // Opcional: só checa conflito com ativos

        if ($ignorarId) {
            $query->where('id', '!=', $ignorarId);
        }

        $horariosExistentes = $query->get();

        // 3. Compara com cada horário existente
        foreach ($horariosExistentes as $existente) {
            $existenteInicio = Carbon::parse($existente->horario_inicio);
            $existenteFim = $existenteInicio->copy()->addMinutes($existente->duracao_minutos);

            // Lógica de Colisão de Intervalos:
            // (InicioA < FimB) E (FimA > InicioB)
            // Se isso for verdade, os horários se sobrepõem.
            if ($novoInicio->lessThan($existenteFim) && $novoFim->greaterThan($existenteInicio)) {
                return true; // Encontrou conflito
            }
        }

        return false; // Sem conflitos
    }

    /**
     * Atualiza as aulas agendadas no futuro para refletir a mudança no horário base.
     */
    private function sincronizarAulasFuturas($horarioAgenda, $novoInicio, $novaDuracao)
    {
        $dataAtual = Carbon::now();

        Aula::where('horario_agenda_id', $horarioAgenda->id)
            ->where('data_hora_inicio', '>', $dataAtual)
            ->where('status', 'agendada') // Só altera aulas agendadas
            ->get()
            ->each(function ($aula) use ($novoInicio, $novaDuracao) {
                // Mantém a data original da aula, muda apenas a hora
                $dataOriginal = Carbon::parse($aula->data_hora_inicio)->format('Y-m-d');
                $novaDataHora = Carbon::parse($dataOriginal . ' ' . $novoInicio);

                $aula->update([
                    'data_hora_inicio' => $novaDataHora,
                    'duracao_minutos' => $novaDuracao,
                ]);
            });
    }
}
