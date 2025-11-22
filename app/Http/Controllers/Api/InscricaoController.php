<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInscricaoRequest;
use App\Http\Requests\UpdateInscricaoRequest;
use App\Models\Inscricao;
use App\Models\HorarioAluno;
use App\Models\HorarioAgenda;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;


class InscricaoController extends Controller
{
    /**
     * Lista todas as inscrições com seus relacionamentos.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Inscricao::with(['usuario', 'plano']);

        // Regra de Filtro: Se o botão "Ver Contratos" enviou o ID, filtra.
        if ($request->has('usuario_id')) {
            $query->where('usuario_id', $request->input('usuario_id'));
        }

        $inscricoes = $query->orderBy('data_inicio', 'desc')->get();
        return response()->json(['data' => $inscricoes]);
    }

    public function show($id): JsonResponse
    {
        // Carrega horários fixos e os dados da agenda (essencial para a tela de edição)
        return response()->json(Inscricao::with(['usuario', 'plano', 'horariosAluno.agenda'])->findOrFail($id));
    }
    /**
     * Cria a matrícula validando plano e vagas.
     */
    public function store(StoreInscricaoRequest $request): JsonResponse
    {
        $dadosValidados = $request->validated();

        try {
            $inscricao = DB::transaction(function () use ($dadosValidados) {

                // 1. Cria o Contrato
                $inscricao = Inscricao::create($dadosValidados);

                // 2. Reserva as Vagas (HorariosAluno) e Checagem de Capacidade
                foreach ($dadosValidados['horarios_agenda_ids'] as $agendaId) {

                    $horarioAgenda = HorarioAgenda::findOrFail($agendaId);
                    $ocupacaoAtual = HorarioAluno::where('horario_agenda_id', $agendaId)->where('status', 'ativo')->count();

                    if ($ocupacaoAtual >= $horarioAgenda->vagas_totais) {
                        throw new \Exception("Vaga indisponível para o horário ID: {$agendaId}");
                    }

                    HorarioAluno::create([
                        'inscricao_id' => $inscricao->id,
                        'horario_agenda_id' => $agendaId,
                        'status' => 'ativa'
                    ]);
                }

                // 3. Geração Inicial de Aulas (Obrigatório, 60 dias)
                $inscricao->gerarAulasFuturas(60);

                return $inscricao;
            });

            return response()->json(['message' => 'Matrícula realizada e aulas geradas!', 'data' => $inscricao], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Falha na matrícula: ' . $e->getMessage()], 422);
        }
    }

    public function update(UpdateInscricaoRequest $request, $id): JsonResponse
    {
        $inscricao = Inscricao::findOrFail($id);
        $dadosValidados = $request->validated();
        $novoStatus = $dadosValidados['status'];
        $statusAnterior = $inscricao->status;

        try {
            DB::transaction(function () use ($inscricao, $dadosValidados, $novoStatus, $statusAnterior) {

                // 1. LÓGICA DE CHECAGEM DE VAGA PARA REATIVAÇÃO (Se o Admin reativar)
                if ($novoStatus === 'ativa' && $statusAnterior !== 'ativa') {
                    $inscricao->load('horariosAluno.agenda');

                    foreach ($inscricao->horariosAluno as $vinculo) {
                        $ocupacaoAtual = HorarioAluno::where('horario_agenda_id', $vinculo->horario_agenda_id)->where('status', 'ativo')->count();

                        if ($ocupacaoAtual >= $vinculo->agenda->vagas_totais) {
                            // Se a turma estiver cheia, lança exceção e aborta
                            throw new \Exception("Vaga indisponível para o horário {$this->formatarDia($vinculo->agenda->dia_semana)} às {$vinculo->agenda->horario_inicio}. Turma cheia.");
                        }
                    }
                }

                // 2. Atualiza o Contrato
                $inscricao->update($dadosValidados);

                // 3. Lógica de Inativação/Reativação de Vagas
                if ($novoStatus === 'ativa') {
                    $inscricao->horariosAluno()->update(['status' => 'ativo']); // Ativa os vínculos
                } else {
                    $inscricao->horariosAluno()->update(['status' => 'inativo']); // Libera as vagas
                }

                // 4. (Opcional) Chamar a geração de aulas para revalidar o calendário
                $inscricao->gerarAulasFuturas(60);
            });

            return response()->json($inscricao->fresh(['usuario', 'plano']), 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Falha ao atualizar a inscrição. ' . $e->getMessage(),
            ], 422);
        }
    }

    // Auxiliar para mensagem de erro bonita
    private function formatarDia($dia) {
        $dias = [1=>'Segunda', 2=>'Terça', 3=>'Quarta', 4=>'Quinta', 5=>'Sexta', 6=>'Sábado', 7=>'Domingo'];
        return $dias[$dia] ?? 'Dia inválido';
    }
}

