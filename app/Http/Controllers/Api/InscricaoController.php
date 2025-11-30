<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInscricaoRequest;
use App\Http\Requests\UpdateInscricaoRequest;
use App\Models\Inscricao;
use App\Models\Mensalidade;
use App\Models\HorarioAluno;
use App\Models\HorarioAgenda;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InscricaoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Inscricao::with(['usuario', 'plano']);
        if ($request->has('usuario_id')) {
            $query->where('usuario_id', $request->input('usuario_id'));
        }
        return response()->json(['data' => $query->orderBy('data_inicio', 'desc')->get()]);
    }

    public function show($id): JsonResponse
    {
        return response()->json(Inscricao::with(['usuario', 'plano', 'horariosAluno'])->findOrFail($id));
    }

    public function store(StoreInscricaoRequest $request): JsonResponse
    {
        // ... (código do store mantido igual ao anterior) ...
        // Vou omitir aqui para focar no update, mas mantenha o que fizemos antes.
        $dadosValidados = $request->validated();

        try {
            // Validação de Vagas
            $horariosAValidar = HorarioAgenda::whereIn('id', $dadosValidados['horarios_agenda_ids'])
                ->withCount(['horariosAluno as ocupacao' => function ($query) {
                    $query->where('horarios_aluno.status', 'ativo');
                }])->get();

            foreach ($horariosAValidar as $horario) {
                if ($horario->ocupacao >= $horario->vagas_totais) {
                    throw new \Exception("Vaga indisponível para o horário das {$horario->horario_inicio}.");
                }
            }

            $inscricao = DB::transaction(function () use ($dadosValidados) {
                $inscricao = Inscricao::create([
                    'usuario_id' => $dadosValidados['usuario_id'],
                    'plano_id' => $dadosValidados['plano_id'],
                    'data_inicio' => $dadosValidados['data_inicio'],
                    'status' => 'ativa',
                ]);

                $pivotData = [];
                $now = Carbon::now();
                foreach ($dadosValidados['horarios_agenda_ids'] as $id) {
                    $pivotData[] = [
                        'inscricao_id' => $inscricao->id,
                        'horario_agenda_id' => $id,
                        'status' => 'ativo',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                DB::table('horarios_aluno')->insert($pivotData);

                $inscricao->gerarAulasFuturas();

                // GERA A PRIMEIRA MENSALIDADE
                $inscricao->load('plano');
                $primeiroVencimento = Carbon::parse($dadosValidados['data_inicio'])->addMonth()->day(10);

                Mensalidade::create([
                    'inscricao_id' => $inscricao->id,
                    'data_vencimento' => $primeiroVencimento,
                    'valor' => $inscricao->plano->preco,
                    'status' => 'pendente'
                ]);

                return $inscricao;
            });

            return response()->json($inscricao->load(['usuario', 'plano', 'horariosAluno']), 201);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Atualiza o Contrato, Horários, Aulas e MENSALIDADES.
     */
    public function update(UpdateInscricaoRequest $request, $id): JsonResponse
    {
        $dadosValidados = $request->validated();
        $inscricao = Inscricao::findOrFail($id);
        $inscricaoId = $inscricao->id;

        try {
            DB::transaction(function () use ($inscricao, $dadosValidados, $inscricaoId) {

                $inscricao->update([
                    'plano_id' => $dadosValidados['plano_id'],
                    'status' => $dadosValidados['status'],
                ]);

                $statusPivo = ($dadosValidados['status'] === 'ativa' || $dadosValidados['status'] === 'trancada') ? 'ativo' : 'inativo';
                $now = Carbon::now();

                // --- CORREÇÃO DE AULAS (ADICIONADO) ---
                // Cancela TODAS as aulas futuras antes de mudar os horários.
                // Isso garante que as aulas dos horários antigos deixem de estar "agendadas".
                $inscricao->deletarAulasFuturas();

                // 1. Remove antigos vínculos
                DB::table('horarios_aluno')->where('inscricao_id', $inscricaoId)->delete();

                // 2. Insere novos vínculos
                $insertData = [];
                foreach ($dadosValidados['horarios_agenda_ids'] as $idAgenda) {
                    $insertData[] = [
                        'inscricao_id' => $inscricaoId,
                        'horario_agenda_id' => $idAgenda,
                        'status' => $statusPivo,
                        'created_at' => $now,
                        'updated_at' => $now
                    ];
                }
                if (!empty($insertData)) {
                    DB::table('horarios_aluno')->insert($insertData);
                }

                $inscricao->refresh();

                // 3. GESTÃO DE AULAS E MENSALIDADES
                if ($dadosValidados['status'] === 'ativa') {
                    // A. Gera Aulas (Para os NOVOS horários)
                    // Nota: A função gerarAulasFuturas que ajustamos antes vai pegar as aulas
                    // que acabamos de cancelar e REATIVAR apenas se elas baterem com o novo horário.
                    $inscricao->gerarAulasFuturas();

                    // B. Gera Mensalidade Futura (Lógica mantida...)
                    $proximoVencimento = Carbon::now()->addMonth()->day(10);

                    $existeMensalidade = Mensalidade::where('inscricao_id', $inscricaoId)
                        ->whereMonth('data_vencimento', $proximoVencimento->month)
                        ->whereYear('data_vencimento', $proximoVencimento->year)
                        ->exists();

                    if (!$existeMensalidade) {
                        $inscricao->load('plano');
                        Mensalidade::create([
                            'inscricao_id' => $inscricaoId,
                            'data_vencimento' => $proximoVencimento,
                            'valor' => $inscricao->plano->preco,
                            'status' => 'pendente'
                        ]);
                    }

                } else {
                    // Se não for ativa, já cancelamos tudo lá em cima,
                    // mas limpamos as mensalidades pendentes aqui.
                    Mensalidade::where('inscricao_id', $inscricaoId)
                        ->where('status', 'pendente')
                        ->where('data_vencimento', '>=', Carbon::now())
                        ->delete();
                }
            });

            return response()->json($inscricao->fresh(['usuario', 'plano', 'horariosAluno']), 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Falha ao atualizar: ' . $e->getMessage()], 422);
        }
    }

    // Auxiliar para mensagem de erro bonita
    private function formatarDia($dia) {
        $dias = [1=>'Segunda', 2=>'Terça', 3=>'Quarta', 4=>'Quinta', 5=>'Sexta', 6=>'Sábado', 7=>'Domingo'];
        return $dias[$dia] ?? 'Dia Inválido';
    }
}
