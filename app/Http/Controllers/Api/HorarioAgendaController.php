<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HorarioAgenda;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HorarioAgendaController extends Controller
{
    /**
     * Lista todos os horários da agenda.
     */
    public function index()
    {
        if (Auth::user()->tipo !== 'admin') {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }

        $horarios = HorarioAgenda::orderBy('dia_semana')->orderBy('horario_inicio')->get();
        return response()->json($horarios);
    }

    /**
     * Cria um novo horário no template da agenda.
     */
    public function store(Request $request)
    {
        if (Auth::user()->tipo !== 'admin') {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }

        $dadosValidados = $request->validate([
            'dia_semana' => 'required|integer|between:1,6',
            'horario_inicio' => 'required|date_format:H:i',
        ]);

        $horarioExistente = HorarioAgenda::where('dia_semana', $dadosValidados['dia_semana'])
                                        ->where('horario_inicio', $dadosValidados['horario_inicio'])
                                        ->first();

        if ($horarioExistente) {
            return response()->json(['message' => 'Este horário já está cadastrado.'], 409);
        }

        $horario = HorarioAgenda::create($dadosValidados);

        return response()->json($horario, 201);
    }

    /**
     * Mostra os detalhes de um horário específico.
     */
    public function show(HorarioAgenda $horariosAgenda) // Laravel faz o findOrFail() automaticamente
    {
        if (Auth::user()->tipo !== 'admin') {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }
        return response()->json($horariosAgenda);
    }

    /**
     * Atualiza um horário, útil para alocar ou desalocar um aluno.
     */
    public function update(Request $request, HorarioAgenda $horariosAgenda)
    {
        if (Auth::user()->tipo !== 'admin') {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }

        $dadosValidados = $request->validate([
            // Permite que o ID da inscrição seja nulo (para libertar o horário)
            // ou que seja um ID válido que exista na tabela de inscrições.
            'inscricao_id' => 'nullable|exists:inscricoes,id',
        ]);

        $horariosAgenda->update($dadosValidados);

        return response()->json($horariosAgenda);
    }

    /**
     * Remove um horário do template da agenda.
     */
    public function destroy(HorarioAgenda $horariosAgenda)
    {
        if (Auth::user()->tipo !== 'admin') {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }

        if ($horariosAgenda->inscricao_id) {
            return response()->json(['message' => 'Não é possível apagar um horário que está ocupado. Remova o aluno deste horário primeiro.'], 403);
        }

        $horariosAgenda->delete();

        return response()->json(null, 204);
    }
}
