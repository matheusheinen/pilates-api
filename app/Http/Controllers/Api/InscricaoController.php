<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreInscricaoRequest;
use App\Http\Requests\UpdateInscricaoRequest;
use Illuminate\Http\JsonResponse;
use App\Models\HorarioFixo;
use App\Models\Inscricao;
use App\Models\Plano;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;

class InscricaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $inscricoes = Inscricao::with(['usuario', 'plano'])->get();

        return response()->json($inscricoes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInscricaoRequest $request): JsonResponse
    {
        // 1. Obtém os dados que já passaram pela validação do StoreInscricaoRequest
        $dadosValidados = $request->validated();
        $horariosParaAgendar = $dadosValidados['horarios'];

        // --- VERIFICAÇÃO DAS REGRAS DE NEGÓCIO ---
        foreach ($horariosParaAgendar as $horario) {
            // 2. Verifica se o horário desejado já atingiu o limite de 3 alunos.
            $alunosNoHorario = HorarioFixo::where('dia_da_semana', $horario['dia_da_semana'])
                                          ->where('horario', $horario['horario'])
                                          ->count();

            if ($alunosNoHorario >= 3) {
                return response()->json([
                    'message' => "O horário de {$horario['horario']} no dia da semana {$horario['dia_da_semana']} já está cheio."
                ], 409); // 409 Conflict
            }

            // 3. Verifica se já existem 2 aulas a acontecer em simultâneo.
            $aulasEmSimultaneo = HorarioFixo::where('dia_da_semana', $horario['dia_da_semana'])
                                            ->where('horario', '<', $horario['horario_fim'])
                                            ->where('horario_fim', '>', $horario['horario'])
                                            ->count();

            if ($aulasEmSimultaneo >= 2) {
                return response()->json([
                    'message' => "O limite de 2 aulas em simultâneo foi atingido para o horário entre {$horario['horario']} e {$horario['horario_fim']}."
                ], 409); // 409 Conflict
            }
        }
        // --- FIM DA VERIFICAÇÃO ---


        // 4. Usa uma transação para garantir a integridade dos dados.
        // Se algo falhar, tudo é revertido.
        DB::beginTransaction();
        try {
            // Cria a inscrição principal
            $inscricao = Inscricao::create([
                'usuario_id' => $dadosValidados['usuario_id'],
                'plano_id' => $dadosValidados['plano_id'],
                'data_inicio' => $dadosValidados['data_inicio'],
            ]);

            // Percorre a lista de horários e cria cada um, associando-o à inscrição.
            foreach ($horariosParaAgendar as $horario) {
                $inscricao->horariosFixos()->create($horario);
            }

            // Se tudo correu bem, confirma as operações na base de dados.
            DB::commit();

            // Retorna a inscrição criada, incluindo os horários fixos.
            return response()->json($inscricao->load('horariosFixos'), 201);

        // ...
        } catch (\Exception $e) {
            // Se algo deu errado, reverte todas as operações.
            DB::rollBack();

            // Retorna um erro MAIS DETALHADO para depuração.
            return response()->json([
                'message' => 'Ocorreu um erro ao criar a inscrição.',
            ], 500);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // 1. Encontra a inscrição pelo ID ou falha com um erro 404 Not Found.
        $inscricao = Inscricao::findOrFail($id);

        // 2. Carrega os relacionamentos necessários.
        $inscricao->load(['usuario', 'plano', 'horariosFixos']);

        // 3. Retorna a inscrição com os dados carregados.
        return response()->json($inscricao);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInscricaoRequest $request, string $id)
    {
        $inscricao = Inscricao::findOrFail($id);
        $dadosValidados = $request->validated();

        DB::beginTransaction();
        try {
            // 1. Atualiza a inscrição diretamente com os dados validados.
            // O Form Request já garante que só os campos permitidos ('ativo', 'plano_id', etc.) estão aqui.
            $inscricao->update($dadosValidados);

            // 2. Se um novo conjunto de horários foi enviado, apaga os antigos e cria os novos.
            if (isset($dadosValidados['horarios'])) {
                $inscricao->horariosFixos()->delete();
                foreach ($dadosValidados['horarios'] as $horario) {
                    $inscricao->horariosFixos()->create($horario);
                }
            }

            DB::commit();

            $inscricao->load(['usuario', 'plano', 'horariosFixos']);
            return response()->json($inscricao);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Ocorreu um erro ao atualizar a inscrição.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
