<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Aula;
use App\Http\Requests\UpdateAulaRequest;

class AulaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Aula::query();

        $usuarioLogado = Auth::user();

        if ($usuarioLogado->tipo === 'aluno') {
            $query->where('usuario_id', $usuarioLogado->id);
        } else if ($usuarioLogado->tipo === 'admin') {
            if ($request->has('usuario_id')) {
                $query->where('usuario_id', $request->usuario_id);
            }
        }

        $mes = $request->input('mes', Carbon::now()->format('Y-m'));

        try {
            $dataInicio = Carbon::createFromFormat('Y-m', $mes)->startOfMonth();
            $dataFim = Carbon::createFromFormat('Y-m', $mes)->endOfMonth();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Formato de mês inválido. Use YYYY-MM.'], 400);
        }

        $query->whereBetween('data_aula', [$dataInicio, $dataFim]);

        $aulas = $query->with('usuario')->orderBy('data_aula')->orderBy('horario')->get();

        return response()->json($aulas);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

        $aula = Aula::findOrFail($id);

        $usuarioLogado = Auth::user();
        if ($usuarioLogado->tipo === 'aluno' && $aula->usuario_id !== $usuarioLogado->id) {

            return response()->json(['message' => 'Acesso não autorizado.'], 403);
        }

        $aula->load('usuario');

        return response()->json($aula);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAulaRequest $request, string $id)
    {

        $aula = Aula::findOrFail($id);
        $dadosValidados = $request->validated();
        $usuarioLogado = Auth::user();


        if ($usuarioLogado->tipo === 'aluno' && (isset($dadosValidados['data_aula']) || isset($dadosValidados['horario']))) {

            $horarioOriginalDaAula = Carbon::parse($aula->data_aula . ' ' . $aula->horario);
            if (Carbon::now()->diffInHours($horarioOriginalDaAula) < 24) {
                return response()->json([
                    'message' => 'O reagendamento só pode ser feito com mais de 24 horas de antecedência.'
                ], 403);
            }


            $novaData = $dadosValidados['data_aula'] ?? $aula->data_aula;
            $novoHorario = $dadosValidados['horario'] ?? $aula->horario;

            $alunosNoNovoHorario = Aula::where('data_aula', $novaData)
                                       ->where('horario', $novoHorario)
                                       ->count();

            if ($alunosNoNovoHorario >= 3) {
                return response()->json(['message' => 'O novo horário escolhido já está cheio.'], 409);
            }
        }


        $aula->update($dadosValidados);

        if (isset($dadosValidados['data_aula']) || isset($dadosValidados['horario'])) {
            $aula->update(['tipo' => 'reposicao']);
        }

        return response()->json($aula->load('usuario'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
