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
use App\Models\HorarioAgenda;
use Illuminate\Support\Facades\DB;

class AulaController extends Controller
{
    /**
     * Lista as aulas.
     * Para o admin, mostra todas as aulas do mês.
     * Para o aluno, mostra apenas as suas aulas.
     */
    public function index(Request $request)
    {
        try {
            $query = Aula::query();
            $usuarioLogado = Auth::user();

            if ($usuarioLogado->tipo === 'aluno') {
                // Busca inscrição ativa. Se não tiver, retorna vazio.
                $inscricao = Inscricao::where('usuario_id', $usuarioLogado->id)
                                      ->where('status', 'ativa')
                                      ->first();

                if ($inscricao) {
                    $query->where('inscricao_id', $inscricao->id);
                } else {
                    return response()->json([]);
                }
            }

            // Filtro de mês (opcional)
            if ($request->has('mes')) {
                $mes = $request->input('mes');
                try {
                    $dataInicio = Carbon::createFromFormat('Y-m', $mes)->startOfMonth();
                    $dataFim = Carbon::createFromFormat('Y-m', $mes)->endOfMonth();
                    $query->whereBetween('data_hora_inicio', [$dataInicio, $dataFim]);
                } catch (\Exception $e) {
                    // Data inválida, ignora filtro
                }
            }

            // Carrega relacionamentos
            $aulas = $query->with(['usuario', 'horarioAgenda', 'inscricao.plano'])
                           ->orderBy('data_hora_inicio')
                           ->get();

            // Mapeamento seguro (BLINDADO CONTRA NULOS)
            $eventosAgregados = $aulas->map(function ($aula) {
                // Garante que data_hora_inicio seja Carbon
                $inicio = $aula->data_hora_inicio instanceof Carbon
                    ? $aula->data_hora_inicio
                    : Carbon::parse($aula->data_hora_inicio);

                return [
                    'id' => $aula->id,
                    'title' => $aula->usuario?->nome ?? 'Aluno', // Nullsafe
                    'start' => $inicio->toIso8601String(),
                    'end' => $inicio->copy()->addMinutes($aula->duracao_minutos)->toIso8601String(),
                    'backgroundColor' => $aula->status === 'realizada' ? '#10B981' : ($aula->status === 'cancelada' ? '#EF4444' : '#3B82F6'),
                    'borderColor' => $aula->status === 'realizada' ? '#059669' : ($aula->status === 'cancelada' ? '#B91C1C' : '#2563EB'),
                    'extendedProps' => [
                        'status' => $aula->status,
                        'usuario_id' => $aula->usuario_id,
                        // Nullsafe operator (?->) evita o erro 500 se não tiver plano
                        'plano' => $aula->inscricao?->plano?->nome ?? 'N/A',
                        'data_aula' => $inicio->format('Y-m-d'),
                        'horario_agenda_id' => $aula->horario_agenda_id,
                    ]
                ];
            });

            return response()->json($eventosAgregados);

        } catch (\Exception $e) {
            // Log do erro real para debug
            Log::error("Erro ao listar aulas: " . $e->getMessage());
            return response()->json(['message' => 'Erro interno ao buscar aulas.'], 500);
        }
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
    public function disponiveisParaReagendamento(Request $request)
    {
        $dataBase = $request->input('data_base') ? Carbon::parse($request->input('data_base')) : Carbon::now();
        $aulaId = $request->input('aula_id');
        $aulaOriginal = $aulaId ? Aula::find($aulaId) : null;

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

                        if ($aulaOriginal && $aulaOriginal->data_hora_inicio->eq($dataHoraSlot)) {
                            continue;
                        }

                        if ($dataHoraSlot->gt($agora->copy()->addHour())) {
                            $ocupacao = Aula::where('data_hora_inicio', $dataHoraSlot)
                                ->where('horario_agenda_id', $slot->id)
                                ->where('status', 'agendada')
                                ->count();

                            if ($ocupacao < $slot->vagas_totais) {
                                $horariosDisponiveis[] = [
                                    'id_agenda' => $slot->id,
                                    'data_hora' => $dataHoraSlot->toIso8601String(),
                                    'dia_semana' => $diaAtual->locale('pt-BR')->dayName,
                                    'horario' => $slot->horario_inicio,
                                    'duracao' => $slot->duracao_minutos, // <--- ADICIONADO AQUI
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

    /**
     * Realiza o reagendamento.
     */
    public function reagendar(Request $request, $id)
    {
        $request->validate([
            'nova_data_hora' => 'required|date',
            'id_agenda_destino' => 'required|exists:horarios_agenda,id'
        ]);

        $aulaOriginal = Aula::findOrFail($id);
        $usuarioLogado = Auth::user();

        // 1. Validação de Propriedade
        if ($aulaOriginal->usuario_id !== $usuarioLogado->id) {
            return response()->json(['message' => 'Não autorizado.'], 403);
        }

        // 2. Validação de 24h (Regra de Negócio)
        // O aluno só pode reagendar se faltar mais de 24h para a aula original
        $horasParaAula = Carbon::now()->diffInHours($aulaOriginal->data_hora_inicio, false);

        // Se a aula já passou ou é daqui a menos de 24h, bloqueia.
        // Exceção: Se a aula já estava cancelada, talvez você queira permitir reagendar?
        // O prompt diz: "reagendamento só pode ser feito antes de 24 horas do começo da aula".
        // Assumimos que isso vale mesmo se ela estiver cancelada (reabrir vaga).
        if ($horasParaAula < 24) {
            return response()->json(['message' => 'O reagendamento só é permitido com 24h de antecedência.'], 422);
        }

        try {
            DB::transaction(function () use ($aulaOriginal, $request, $usuarioLogado) {

                // 3. Atualiza a aula antiga para 'reagendada'
                $aulaOriginal->update(['status' => 'reagendada']);

                // 4. Cria a nova aula no horário escolhido
                // Verifica se já existe o registro de aula para aquele horário (pode estar criado para outro aluno)
                // A tabela 'aulas' registra INDIVIDUALMENTE a aula do aluno, então sempre criamos um novo registro
                // ou atualizamos um existente se o sistema usasse slots compartilhados (mas seu modelo parece ser 1 registro por aluno).

                Aula::create([
                    'inscricao_id' => $aulaOriginal->inscricao_id, // Mantém o vínculo contratual
                    'usuario_id' => $usuarioLogado->id,
                    'horario_agenda_id' => $request->id_agenda_destino, // Novo slot
                    'horario_aluno_id' => null, // É uma aula avulsa/reposta, não fixa
                    'data_hora_inicio' => Carbon::parse($request->nova_data_hora),
                    'duracao_minutos' => $aulaOriginal->duracao_minutos,
                    'status' => 'agendada',
                    'observacoes' => 'Reagendamento da aula de ' . $aulaOriginal->data_hora_inicio->format('d/m H:i')
                ]);

            });

            return response()->json(['message' => 'Aula reagendada com sucesso!']);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao reagendar: ' . $e->getMessage()], 500);
        }
    }

    public function listagemCalendario(Request $request)
    {
        // Auto-conclusão de aulas passadas
        Aula::where('status', 'agendada')
            ->where('data_hora_inicio', '<', Carbon::now())
            ->update(['status' => 'realizada']);

        $dataInicio = Carbon::parse($request->input('start'))->startOfDay();
        $dataFim = Carbon::parse($request->input('end'))->endOfDay();

        $aulas = Aula::with(['usuario:id,nome,celular', 'horarioAgenda:id,vagas_totais'])
            ->whereBetween('data_hora_inicio', [$dataInicio, $dataFim])
            ->get();

        $eventosAgregados = $aulas->groupBy(function ($aula) {
            return $aula->data_hora_inicio->format('Y-m-d') . '_' . $aula->horario_agenda_id;
        })->map(function ($aulasDoSlot) {

            $primeiraAula = $aulasDoSlot->first();

            // Mapeia os detalhes
            $alunosDetalhes = $aulasDoSlot->map(function ($aula) {
                return [
                    'id_inscricao' => $aula->inscricao_id,
                    'nome' => $aula->usuario->nome,
                    'celular' => $aula->usuario->celular,
                    'status' => $aula->status,
                    'id_aula' => $aula->id
                ];
            });

            // AJUSTE CRÍTICO: Contagem de Ocupação
            // Conta apenas 'agendada' ou 'realizada' como ocupando vaga.
            // 'cancelada' e 'reagendada' liberam a vaga visualmente.
            $ocupadas = $aulasDoSlot->filter(function ($a) {
                return in_array($a->status, ['agendada', 'realizada']);
            })->count();

            // Define cor do evento
            $statusGeral = 'agendada';
            if ($aulasDoSlot->every(fn($a) => $a->status === 'realizada')) $statusGeral = 'realizada';
            elseif ($aulasDoSlot->every(fn($a) => $a->status === 'cancelada')) $statusGeral = 'cancelada';

            $colorMap = [
                'realizada' => '#10B981',
                'cancelada' => '#EF4444',
                'agendada' => '#3B82F6'
            ];

            return [
                'id' => $primeiraAula->horario_agenda_id . '_' . $primeiraAula->data_hora_inicio->format('YmdHi'),
                // Título reflete apenas as vagas REAIS ocupadas
                'title' => "Pilates ({$ocupadas}/{$primeiraAula->horarioAgenda->vagas_totais})",
                'start' => $primeiraAula->data_hora_inicio->toIso8601String(),
                'end' => $primeiraAula->data_hora_inicio->addMinutes($primeiraAula->duracao_minutos)->toIso8601String(),
                'backgroundColor' => $colorMap[$statusGeral],
                'borderColor' => $colorMap[$statusGeral],
                'extendedProps' => [
                    'alunos' => $alunosDetalhes,
                    'total_alunos' => $ocupadas, // Envia a contagem real para o frontend
                    'vagas_totais' => $primeiraAula->horarioAgenda->vagas_totais,
                    'data_base' => $primeiraAula->data_hora_inicio->format('Y-m-d'),
                    'horario_agenda_id' => $primeiraAula->horario_agenda_id,
                    'status_geral' => $statusGeral
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

    public function cancelarTurma(Request $request)
    {
        if (Auth::user()->tipo !== 'admin') {
             return response()->json(['message' => 'Não autorizado.'], 403);
        }

        $request->validate([
            'data' => 'required|date',
            'horario_agenda_id' => 'required|exists:horarios_agenda,id'
        ]);

        $dataDate = Carbon::parse($request->data)->format('Y-m-d');

        // Atualiza para 'cancelada' (liberando a vaga na contagem do index)
        $afetados = Aula::where('horario_agenda_id', $request->horario_agenda_id)
            ->whereDate('data_hora_inicio', $dataDate)
            ->where('status', 'agendada')
            ->update(['status' => 'cancelada']);

        return response()->json(['message' => "Turma cancelada. {$afetados} aulas foram canceladas e as vagas liberadas."]);
    }

    /**
     * [RENOMEADO] Reagenda TODAS as aulas de uma turma para outro horário.
     * Rota: POST /api/aulas/reagendar-turma
     */
    public function reagendarTurma(Request $request)
    {
        if (Auth::user()->tipo !== 'admin') {
             return response()->json(['message' => 'Não autorizado.'], 403);
        }

        $request->validate([
            'data_origem' => 'required|date',
            'id_agenda_origem' => 'required|exists:horarios_agenda,id',
            'nova_data' => 'required|date',
            'id_agenda_destino' => 'required|exists:horarios_agenda,id'
        ]);

        try {
            DB::transaction(function () use ($request) {
                $dataOrigem = Carbon::parse($request->data_origem)->format('Y-m-d');

                // Busca aulas ativas
                $aulasOrigem = Aula::where('horario_agenda_id', $request->id_agenda_origem)
                    ->whereDate('data_hora_inicio', $dataOrigem)
                    ->where('status', 'agendada')
                    ->get();

                if ($aulasOrigem->isEmpty()) {
                    throw new \Exception("Nenhuma aula ativa encontrada nesta turma para reagendar.");
                }

                $totalAlunos = $aulasOrigem->count();

                // Verifica capacidade do destino
                $slotDestino = HorarioAgenda::findOrFail($request->id_agenda_destino);
                $novaDataHora = Carbon::parse($request->nova_data . ' ' . $slotDestino->horario_inicio);

                $ocupacaoDestino = Aula::where('horario_agenda_id', $slotDestino->id)
                    ->whereDate('data_hora_inicio', $novaDataHora->format('Y-m-d'))
                    ->where('status', 'agendada')
                    ->count();

                if (($ocupacaoDestino + $totalAlunos) > $slotDestino->vagas_totais) {
                    throw new \Exception("Destino sem vagas suficientes. Livres: " . ($slotDestino->vagas_totais - $ocupacaoDestino));
                }

                // Processa transferência
                foreach ($aulasOrigem as $aula) {
                    // Atualiza antiga para 'reagendada' (libera vaga na origem)
                    $aula->update(['status' => 'reagendada']);

                    // Cria nova 'agendada' (ocupa vaga no destino)
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

            return response()->json(['message' => 'Turma reagendada com sucesso! Vagas da origem liberadas.']);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }



}
