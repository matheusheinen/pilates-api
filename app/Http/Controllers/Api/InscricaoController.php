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
        $dadosValidados = $request->validated();
        $horariosParaAgendar = $dadosValidados['horarios'];


        foreach ($horariosParaAgendar as $horario) {
            $alunosNoHorario = HorarioFixo::where('dia_da_semana', $horario['dia_da_semana'])
                                          ->where('horario', $horario['horario'])
                                          ->count();

            if ($alunosNoHorario >= 3) {
                return response()->json([
                    'message' => "O horário de {$horario['horario']} no dia da semana {$horario['dia_da_semana']} já está cheio."
                ], 409);
            }

            $aulasEmSimultaneo = HorarioFixo::where('dia_da_semana', $horario['dia_da_semana'])
                                            ->where('horario', '<', $horario['horario_fim'])
                                            ->where('horario_fim', '>', $horario['horario'])
                                            ->count();

            if ($aulasEmSimultaneo >= 2) {
                return response()->json([
                    'message' => "O limite de 2 aulas em simultâneo foi atingido para o horário entre {$horario['horario']} e {$horario['horario_fim']}."
                ], 409);
            }
        }

        DB::beginTransaction();
        try {
            $inscricao = Inscricao::create([
                'usuario_id' => $dadosValidados['usuario_id'],
                'plano_id' => $dadosValidados['plano_id'],
                'data_inicio' => $dadosValidados['data_inicio'],
            ]);

            foreach ($horariosParaAgendar as $horario) {
                $inscricao->horariosFixos()->create($horario);
            }

            DB::commit();

            return response()->json($inscricao->load('horariosFixos'), 201);

        // ...
        } catch (\Exception $e) {
            DB::rollBack();
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
        $inscricao = Inscricao::findOrFail($id);

        $inscricao->load(['usuario', 'plano', 'horariosFixos']);

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
            $inscricao->update($dadosValidados);

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
