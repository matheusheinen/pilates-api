<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Aula;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Models\Inscricao;
use App\Models\HorarioAgenda;
use Illuminate\Support\Facades\DB;

class AulaController extends Controller
{
    // ... (Mantenha o método index igual) ...
    public function index(Request $request)
    {
        try {
            $query = Aula::query();
            $usuarioLogado = Auth::user();

            if ($usuarioLogado->tipo === 'aluno') {
                $query->where('usuario_id', $usuarioLogado->id);
            }

            if ($request->has('mes')) {
                $mes = $request->input('mes');
                try {
                    $dataInicio = Carbon::createFromFormat('Y-m', $mes)->startOfMonth();
                    $dataFim = Carbon::createFromFormat('Y-m', $mes)->endOfMonth();
                    $query->whereBetween('data_hora_inicio', [$dataInicio, $dataFim]);
                } catch (\Exception $e) {}
            }

            $aulas = $query->with(['usuario', 'horarioAgenda', 'inscricao.plano'])
                           ->orderBy('data_hora_inicio')
                           ->get();

            $eventosAgregados = $aulas->map(function ($aula) {
                $inicio = $aula->data_hora_inicio instanceof Carbon
                    ? $aula->data_hora_inicio
                    : Carbon::parse($aula->data_hora_inicio);

                return [
                    'id' => $aula->id,
                    'title' => $aula->usuario?->nome ?? 'Aluno',
                    'start' => $inicio->toIso8601String(),
                    // ... (end, colors mantidos) ...
                    'extendedProps' => [
                        'status' => $aula->status,
                        'usuario_id' => $aula->usuario_id,
                        'plano' => $aula->inscricao?->plano?->nome ?? 'Experimental/Avulso',
                        'horario_agenda_id' => $aula->horario_agenda_id,
                        'observacoes' => $aula->observacoes, // <--- ADICIONE ESTA LINHA
                    ]
                ];
            });

            return response()->json($eventosAgregados);

        } catch (\Exception $e) {
            Log::error("Erro ao listar aulas: " . $e->getMessage());
            return response()->json(['message' => 'Erro interno ao buscar aulas.'], 500);
        }
    }

    public function listagemCalendario(Request $request)
    {
        // 1. Atualiza status de aulas passadas
        Aula::where('status', 'agendada')
            ->where('data_hora_inicio', '<', Carbon::now())
            ->update(['status' => 'realizada']);

        // 2. Define datas
        $dataInicio = Carbon::parse($request->input('start'))->startOfDay();
        $dataFim = Carbon::parse($request->input('end'))->endOfDay();

        // 3. Busca aulas (CORREÇÃO: Removemos as reagendadas da visualização)
        $aulas = Aula::with(['usuario:id,nome,celular', 'horarioAgenda:id,vagas_totais'])
            ->whereBetween('data_hora_inicio', [$dataInicio, $dataFim])
            ->where('status', '!=', 'reagendada') // <--- ISSO FAZ ELAS SUMIREM DA AGENDA
            ->get();

        // 4. Agrupa e Mapeia
        $eventosAgregados = $aulas->groupBy(function ($aula) {
            return $aula->data_hora_inicio->format('Y-m-d') . '_' . $aula->horario_agenda_id;
        })->map(function ($aulasDoSlot) {

            $primeiraAula = $aulasDoSlot->first();

            $alunosDetalhes = $aulasDoSlot->map(function ($aula) {
                $ehPrimeiraAula = is_null($aula->inscricao_id) ||
                                  str_contains(strtolower($aula->observacoes ?? ''), 'primeira aula');

                return [
                    'id_inscricao' => $aula->inscricao_id,
                    'nome' => $aula->usuario->nome,
                    'celular' => $aula->usuario->celular,
                    'status' => $aula->status,
                    'id_aula' => $aula->id,
                    'eh_primeira_aula' => $ehPrimeiraAula
                ];
            });

            $temAlunoNovo = $alunosDetalhes->contains('eh_primeira_aula', true);

            $ocupadas = $aulasDoSlot->filter(function ($a) {
                return in_array($a->status, ['agendada', 'realizada']);
            })->count();

            $statusGeral = 'agendada';
            if ($aulasDoSlot->every(fn($a) => $a->status === 'realizada')) $statusGeral = 'realizada';
            elseif ($aulasDoSlot->every(fn($a) => $a->status === 'cancelada')) $statusGeral = 'cancelada';

            $colorMap = ['realizada' => '#10B981', 'cancelada' => '#EF4444', 'agendada' => '#3B82F6'];

            return [
                'id' => $primeiraAula->horario_agenda_id . '_' . $primeiraAula->data_hora_inicio->format('YmdHi'),
                'title' => "Pilates ({$ocupadas}/{$primeiraAula->horarioAgenda->vagas_totais})",
                'start' => $primeiraAula->data_hora_inicio->toIso8601String(),
                'end' => $primeiraAula->data_hora_inicio->copy()->addMinutes($primeiraAula->duracao_minutos)->toIso8601String(),
                'backgroundColor' => $colorMap[$statusGeral],
                'borderColor' => $colorMap[$statusGeral],
                'extendedProps' => [
                    'alunos' => $alunosDetalhes,
                    'total_alunos' => $ocupadas,
                    'vagas_totais' => $primeiraAula->horarioAgenda->vagas_totais,
                    'data_base' => $primeiraAula->data_hora_inicio->format('Y-m-d'),
                    'horario_agenda_id' => $primeiraAula->horario_agenda_id,
                    'status_geral' => $statusGeral,
                    'tem_aluno_novo' => $temAlunoNovo
                ]
            ];
        })->values();

        return response()->json($eventosAgregados);
    }

    // --- NOVO MÉTODO: Cancelar Aula Individual (Botão X) ---
    public function cancelar($id)
    {
        $aula = Aula::findOrFail($id);
        $usuarioLogado = Auth::user();

        // Permite se for Admin OU se for o próprio aluno dono da aula
        if ($usuarioLogado->tipo !== 'admin' && $aula->usuario_id !== $usuarioLogado->id) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        // Atualiza status para cancelada
        $aula->update(['status' => 'cancelada']);

        return response()->json(['message' => 'Aula cancelada com sucesso.']);
    }

    // --- ATUALIZAÇÃO: Método Reagendar Individual ---
    public function reagendar(Request $request, $id)
    {
        $request->validate([
            'nova_data_hora' => 'required|date',
            'id_agenda_destino' => 'required|exists:horarios_agenda,id'
        ]);

        $aulaOriginal = Aula::findOrFail($id);
        $usuarioLogado = Auth::user();

        // Verificações de permissão
        if ($usuarioLogado->tipo !== 'admin' && $aulaOriginal->usuario_id !== $usuarioLogado->id) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        $novaData = Carbon::parse($request->nova_data_hora);

        // Verificação de conflito
        $jaTemAula = Aula::where('usuario_id', $aulaOriginal->usuario_id)
            ->where('status', 'agendada')
            ->whereBetween('data_hora_inicio', [
                $novaData->copy()->subMinutes(1),
                $novaData->copy()->addMinutes(1)
            ])
            ->where('id', '!=', $id)
            ->exists();

        if ($jaTemAula) return response()->json(['message' => 'O aluno já possui uma aula agendada para este horário.'], 422);

        try {
            DB::transaction(function () use ($aulaOriginal, $request, $usuarioLogado, $novaData) {

                // 1. AULA ANTIGA (A que fica amarela):
                // AQUI ESTÁ A CORREÇÃO: A mensagem agora aponta para o FUTURO
                $aulaOriginal->update([
                    'status' => 'reagendada',
                    'observacoes' => 'Nova data: ' . $novaData->format('d/m \à\s H:i')
                ]);

                // 2. AULA NOVA (A verde/azul):
                // Nasce limpa, apenas com log interno
                Aula::create([
                    'inscricao_id' => $aulaOriginal->inscricao_id,
                    'usuario_id' => $aulaOriginal->usuario_id,
                    'horario_agenda_id' => $request->id_agenda_destino,
                    'horario_aluno_id' => null,
                    'data_hora_inicio' => $novaData,
                    'duracao_minutos' => $aulaOriginal->duracao_minutos,
                    'status' => 'agendada',
                    'observacoes' => 'Reagendamento realizado por ' . $usuarioLogado->nome
                ]);
            });
            return response()->json(['message' => 'Aula reagendada com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao reagendar: ' . $e->getMessage()], 500);
        }
    }

    // ... (Mantenha os outros métodos: listarDisponiveis, disponiveisPublico, disponiveisParaReagendamento, cancelarTurma, reagendarTurma) ...
    public function listarDisponiveis()
    {
        $horariosDisponiveis = Aula::where('status', 'disponivel')
                                   ->where('data_hora_inicio', '>', now())
                                   ->orderBy('data_hora_inicio')
                                   ->get();
        return response()->json($horariosDisponiveis);
    }

    public function disponiveisPublico()
    {
        $hoje = Carbon::now();
        $fimPeriodo = $hoje->copy()->addDays(14);
        $slotsAgenda = HorarioAgenda::where('status', 'ativo')->get();
        $horariosDisponiveis = [];

        for ($date = $hoje->copy(); $date->lte($fimPeriodo); $date->addDay()) {
            $diaSemanaIso = $date->dayOfWeekIso;

            foreach ($slotsAgenda as $slot) {
                if ($slot->dia_semana == $diaSemanaIso) {
                    $dataHoraAula = Carbon::parse($date->format('Y-m-d') . ' ' . $slot->horario_inicio);
                    if ($dataHoraAula->isPast()) continue;

                    $ocupacao = Aula::where('horario_agenda_id', $slot->id)
                        ->where('data_hora_inicio', $dataHoraAula->format('Y-m-d H:i:s'))
                        ->where('status', 'agendada')
                        ->count();

                    if ($ocupacao < $slot->vagas_totais) {
                        $horariosDisponiveis[] = [
                            'id_agenda' => $slot->id,
                            'data_hora' => $dataHoraAula->toIso8601String(),
                            'dia_semana' => $date->locale('pt-BR')->dayName,
                            'horario' => $slot->horario_inicio,
                            'duracao' => $slot->duracao_minutos,
                            'vagas_restantes' => $slot->vagas_totais - $ocupacao
                        ];
                    }
                }
            }
        }
        usort($horariosDisponiveis, fn($a, $b) => $a['data_hora'] <=> $b['data_hora']);
        return response()->json($horariosDisponiveis);
    }

    public function disponiveisParaReagendamento(Request $request)
    {
        $dataBase = $request->input('data_base') ? Carbon::parse($request->input('data_base')) : Carbon::now();
        $aulaId = $request->input('aula_id');
        $aulaOriginal = $aulaId ? Aula::find($aulaId) : null;
        $usuarioLogado = Auth::user();

        $inicioSemana = $dataBase->copy()->startOfWeek(Carbon::SUNDAY);
        $fimSemana = $dataBase->copy()->endOfWeek(Carbon::SATURDAY);
        $agora = Carbon::now();

        $slotsAgenda = HorarioAgenda::where('status', 'ativo')->get();
        $horariosDisponiveis = [];
        $diaAtual = $inicioSemana->copy();

        while ($diaAtual->lte($fimSemana)) {
            if ($diaAtual->isToday() || $diaAtual->isFuture()) {
                $diaSemanaIso = $diaAtual->dayOfWeekIso;

                foreach ($slotsAgenda as $slot) {
                    if ($slot->dia_semana == $diaSemanaIso) {
                        $dataHoraSlot = Carbon::parse($diaAtual->format('Y-m-d') . ' ' . $slot->horario_inicio);

                        if ($aulaOriginal && $aulaOriginal->data_hora_inicio->eq($dataHoraSlot)) continue;

                        $temConflito = Aula::where('usuario_id', $usuarioLogado->id)
                            ->where('status', 'agendada')
                            ->whereBetween('data_hora_inicio', [
                                $dataHoraSlot->copy()->subMinutes(1),
                                $dataHoraSlot->copy()->addMinutes(1)
                            ])
                            ->when($aulaId, function($q) use ($aulaId) {
                                $q->where('id', '!=', $aulaId);
                            })
                            ->exists();

                        if ($temConflito) continue;

                        if ($dataHoraSlot->gt($agora->copy()->addHour())) {
                            $ocupacao = Aula::where('data_hora_inicio', $dataHoraSlot->format('Y-m-d H:i:s'))
                                ->where('horario_agenda_id', $slot->id)
                                ->where('status', 'agendada')
                                ->count();

                            if ($ocupacao < $slot->vagas_totais) {
                                $horariosDisponiveis[] = [
                                    'id_agenda' => $slot->id,
                                    'data_hora' => $dataHoraSlot->toIso8601String(),
                                    'dia_semana' => $diaAtual->locale('pt-BR')->dayName,
                                    'horario' => $slot->horario_inicio,
                                    'duracao' => $slot->duracao_minutos,
                                    'vagas_restantes' => $slot->vagas_totais - $ocupacao
                                ];
                            }
                        }
                    }
                }
            }
            $diaAtual->addDay();
        }
        usort($horariosDisponiveis, fn($a, $b) => $a['data_hora'] <=> $b['data_hora']);
        return response()->json($horariosDisponiveis);
    }
    public function atualizarAgenda(Request $request)
    {
        if (Auth::user()->tipo !== 'admin') return response()->json(['message' => 'Acesso não autorizado'], 403);
        try {
            Artisan::call('agenda:atualizar');
            return response()->json(['message' => 'Agenda atualizada com sucesso.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro interno.', 'error_detail' => $e->getMessage()], 500);
        }
    }

    public function cancelarTurma(Request $request)
    {
        if (Auth::user()->tipo !== 'admin') return response()->json(['message' => 'Não autorizado.'], 403);
        $request->validate(['data' => 'required|date', 'horario_agenda_id' => 'required|exists:horarios_agenda,id']);

        $dataDate = Carbon::parse($request->data)->format('Y-m-d');
        $afetados = Aula::where('horario_agenda_id', $request->horario_agenda_id)
            ->whereDate('data_hora_inicio', $dataDate)
            ->where('status', 'agendada')
            ->update(['status' => 'cancelada']);

        return response()->json(['message' => "Turma cancelada. {$afetados} aulas foram canceladas."]);
    }

    public function reagendarTurma(Request $request)
    {
        if (Auth::user()->tipo !== 'admin') return response()->json(['message' => 'Não autorizado.'], 403);

        $request->validate([
            'data_origem' => 'required|date',
            'id_agenda_origem' => 'required|exists:horarios_agenda,id',
            'nova_data' => 'required|date',
            'id_agenda_destino' => 'required|exists:horarios_agenda,id'
        ]);

        try {
            DB::transaction(function () use ($request) {
                $dataOrigem = Carbon::parse($request->data_origem)->format('Y-m-d');
                $aulasOrigem = Aula::where('horario_agenda_id', $request->id_agenda_origem)
                    ->whereDate('data_hora_inicio', $dataOrigem)
                    ->where('status', 'agendada')
                    ->get();

                if ($aulasOrigem->isEmpty()) throw new \Exception("Nenhuma aula ativa nesta turma.");

                $slotDestino = HorarioAgenda::findOrFail($request->id_agenda_destino);
                $novaDataHora = Carbon::parse($request->nova_data);

                $ocupacaoDestino = Aula::where('horario_agenda_id', $slotDestino->id)
                    ->whereDate('data_hora_inicio', $novaDataHora->format('Y-m-d'))
                    ->where('status', 'agendada')
                    ->count();

                if (($ocupacaoDestino + $aulasOrigem->count()) > $slotDestino->vagas_totais) {
                    throw new \Exception("Destino sem vagas suficientes.");
                }

                foreach ($aulasOrigem as $aula) {
                    // 1. ATUALIZA A ANTIGA COM O DESTINO
                    $aula->update([
                        'status' => 'reagendada',
                        'observacoes' => 'Nova data: ' . $novaDataHora->format('d/m \à\s H:i')
                    ]);

                    // 2. CRIA A NOVA
                    Aula::create([
                        'inscricao_id' => $aula->inscricao_id,
                        'usuario_id' => $aula->usuario_id,
                        'horario_agenda_id' => $slotDestino->id,
                        'horario_aluno_id' => null,
                        'data_hora_inicio' => $novaDataHora,
                        'duracao_minutos' => $slotDestino->duracao_minutos,
                        'status' => 'agendada',
                        'observacoes' => 'Reagendamento de Turma (Admin).'
                    ]);
                }
            });
            return response()->json(['message' => 'Turma reagendada com sucesso!']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
