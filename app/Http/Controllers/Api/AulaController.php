<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Aula;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Models\Inscricao; // Garanta que este 'use' está presente

class AulaController extends Controller
{
    /**
     * Lista as aulas.
     * Para o admin, mostra todas as aulas do mês.
     * Para o aluno, mostra apenas as suas aulas.
     */
    public function index(Request $request)
    {
        $query = Aula::query();
        $usuarioLogado = Auth::user();

        if ($usuarioLogado->tipo === 'aluno') {
            $inscricao = Inscricao::where('usuario_id', $usuarioLogado->id)->where('status', 'ativa')->first();
            if ($inscricao) {
                $query->where('inscricao_id', $inscricao->id);
            } else {
                return response()->json([]);
            }
        }

        if ($request->has('mes')) {
            $mes = $request->input('mes');
            try {
                $dataInicio = Carbon::createFromFormat('Y-m', $mes)->startOfMonth();
                $dataFim = Carbon::createFromFormat('Y-m', $mes)->endOfMonth();
                $query->whereBetween('data_hora_inicio', [$dataInicio, $dataFim]);
            } catch (\Exception $e) {}
        }

        $aulas = $query->with(['usuario', 'horario_agenda', 'inscricao.plano'])->get();

        $eventosAgregados = $aulas->map(function ($aula) {
            return [
                'id' => $aula->id,
                'title' => $aula->usuario->nome,
                'start' => $aula->data_hora_inicio,
                'end' => Carbon::parse($aula->data_hora_inicio)->addMinutes($aula->duracao_minutos)->toDateTimeString(),
                'backgroundColor' => $aula->status === 'realizada' ? '#10B981' : ($aula->status === 'cancelada' ? '#EF4444' : '#3B82F6'),
                'borderColor' => $aula->status === 'realizada' ? '#059669' : ($aula->status === 'cancelada' ? '#B91C1C' : '#2563EB'),
                'extendedProps' => [
                    'status' => $aula->status,
                    'usuario_id' => $aula->usuario_id,
                    'plano' => $aula->inscricao->plano->nome ?? 'N/A',
                    'data_aula' => Carbon::parse($aula->data_hora_inicio)->format('Y-m-d'),
                    'horario_agenda_id' => $aula->horario_agenda_id,
                ]
            ];
        });

        return response()->json($eventosAgregados);
    }

    /**
     * Lista todos os horários futuros que estão com o status 'disponivel'.
     */
    public function listarDisponiveis()
    {
        $horariosDisponiveis = Aula::where('status', 'disponivel')
                                   ->where('data_hora_inicio', '>', now())
                                   ->orderBy('data_hora_inicio')
                                   ->get();

        return response()->json($horariosDisponiveis);
    }

    /**
     * Permite que um aluno reagende uma de suas aulas para um novo horário disponível.
     */
    public function reagendar(Request $request, Aula $aula)
    {
        $request->validate(['nova_aula_id' => 'required|exists:aulas,id']);

        $usuarioLogado = Auth::user();

        // --- LINHA CORRIGIDA ---
        $inscricaoDoAluno = $usuarioLogado->inscricoes->where('status', 'ativa')->first();
        // --- FIM DA CORREÇÃO ---

        if (!$inscricaoDoAluno || $aula->inscricao_id !== $inscricaoDoAluno->id) {
            return response()->json(['message' => 'Você não tem permissão para reagendar esta aula.'], 403);
        }

        if (Carbon::now()->diffInHours($aula->data_hora_inicio) < 24) {
            return response()->json(['message' => 'O reagendamento só pode ser feito com mais de 24h de antecedência.'], 403);
        }

        $novoHorario = Aula::findOrFail($request->nova_aula_id);
        if ($novoHorario->status !== 'disponivel' || $novoHorario->inscricao_id !== null) {
            return response()->json(['message' => 'O horário escolhido não está mais disponível.'], 409);
        }

        // Libera a vaga da aula original
        $aula->update([
            'inscricao_id' => null,
            'status' => 'disponivel'
        ]);

        // Ocupa a nova vaga com a inscrição do aluno
        $novoHorario->update([
            'inscricao_id' => $inscricaoDoAluno->id,
            'status' => 'agendada'
        ]);

        return response()->json([
            'message' => 'Aula reagendada com sucesso!',
            'nova_aula' => $novoHorario
        ]);
    }

    public function listagemCalendario(Request $request)
    {
        // FullCalendar envia 'start' e 'end' no formato ISO
        $dataInicio = Carbon::parse($request->input('start'))->startOfDay();
        $dataFim = Carbon::parse($request->input('end'))->endOfDay();

        // 1. Busca todas as aulas no período, agrupando os dados dos alunos
        $aulas = Aula::with(['usuario:id,nome,celular', 'horarioAgenda:id,vagas_totais'])
            ->whereBetween('data_hora_inicio', [$dataInicio, $dataFim])
            ->where('status', 'agendada') // Apenas aulas ativas
            ->get();

        // 2. Agregação: Agrupa as aulas pelo horário e data para criar 1 evento visual
        $eventosAgregados = $aulas->groupBy(function ($aula) {
            // Chave de agrupamento: Data (YYYY-MM-DD) + ID do Slot (HorarioAgenda)
            return $aula->data_hora_inicio->format('Y-m-d') . '_' . $aula->horario_agenda_id;
        })->map(function ($aulasDoSlot) {

            $primeiraAula = $aulasDoSlot->first();

            // 3. Prepara os detalhes dos alunos e a contagem de vagas
            $alunosDetalhes = $aulasDoSlot->map(function ($aula) {
                return [
                    'id_inscricao' => $aula->inscricao_id,
                    'nome' => $aula->usuario->nome,
                    'celular' => $aula->usuario->celular,
                    'status' => $aula->status,
                    'id_aula' => $aula->id // ID da ocorrência individual para cancelamento
                ];
            });

            // 4. Converte para o formato FullCalendar
            return [
                'id' => $primeiraAula->horario_agenda_id . '_' . $primeiraAula->data_hora_inicio->format('YmdHi'),
                'title' => "Pilates ({$aulasDoSlot->count()}/{$primeiraAula->horarioAgenda->vagas_totais})",
                'start' => $primeiraAula->data_hora_inicio->toIso8601String(),
                'end' => $primeiraAula->data_hora_inicio->addMinutes($primeiraAula->duracao_minutos)->toIso8601String(),
                'extendedProps' => [
                    'alunos' => $alunosDetalhes, // Lista completa de alunos
                    'total_alunos' => $aulasDoSlot->count(),
                    'vagas_totais' => $primeiraAula->horarioAgenda->vagas_totais,
                    'data_base' => $primeiraAula->data_hora_inicio->format('Y-m-d'),
                    'horario_agenda_id' => $primeiraAula->horario_agenda_id,
                ]
            ];
        })->values();

        return response()->json($eventosAgregados);
    }


    public function atualizarAgenda(Request $request)
    {
        if (Auth::user()->tipo !== 'admin') {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }

        // CORREÇÃO: Array vazio, pois o comando não pede mais argumentos
        $parametros = [];

        try {
            // Chama o comando 'agenda:atualizar'
            Artisan::call('agenda:atualizar', $parametros);

            $output = Artisan::output();

            return response()->json([
                'message' => 'Agenda atualizada com sucesso (Aulas passadas foram concluídas).',
                'output' => $output
            ]);

        } catch (\Exception $e) {
            Log::error("Falha na atualização manual da agenda: " . $e->getMessage());

            return response()->json([
                'message' => 'Erro interno ao executar a rotina de agenda.',
                'error_detail' => $e->getMessage()
            ], 500);
        }
    }

}
