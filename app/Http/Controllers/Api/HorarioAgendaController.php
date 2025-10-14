<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HorarioAgenda;
use App\Models\Aula;
use Carbon\Carbon;
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
        // ... (validação de admin) ...

        $dadosValidados = $request->validate([
            'dia_semana' => 'required|integer|between:1,6',
            'horario_inicio' => 'required|date_format:H:i',
            'duracao_minutos' => 'required|integer|min:30',
        ]);

        // --- LÓGICA DE VERIFICAÇÃO DE SOBREPOSIÇÃO ---
        $novoInicio = Carbon::parse($dadosValidados['horario_inicio']);
        $novoFim = $novoInicio->copy()->addMinutes($dadosValidados['duracao_minutos']);

        $horariosDoDia = HorarioAgenda::where('dia_semana', $dadosValidados['dia_semana'])->get();

        foreach ($horariosDoDia as $horarioExistente) {
            $inicioExistente = Carbon::parse($horarioExistente->horario_inicio);
            $fimExistente = $inicioExistente->copy()->addMinutes($horarioExistente->duracao_minutos);

            // A sobreposição ocorre se (StartA < EndB) e (EndA > StartB)
            if ($novoInicio->lt($fimExistente) && $novoFim->gt($inicioExistente)) {
                return response()->json([
                    'message' => 'O horário conflita com um horário já existente: ' .
                                $inicioExistente->format('H:i') . ' - ' . $fimExistente->format('H:i')
                ], 409); // 409 Conflict
            }
        }
        // --- FIM DA LÓGICA ---

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
    public function update(Request $request, HorarioAgenda $horarioAgenda)
    {
        if (Auth::user()->tipo !== 'admin') {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }

        $dadosValidados = $request->validate([
            'dia_semana' => 'sometimes|required|integer|between:1,6',
            'horario_inicio' => 'sometimes|required|date_format:H:i',
            'duracao_minutos' => 'sometimes|required|integer|min:30',
            'status' => 'sometimes|required|in:ativo,inativo',
        ]);

        // Lógica para quando um horário é inativado
        if (isset($dadosValidados['status']) && $dadosValidados['status'] === 'inativo') {
            // Cancela todas as aulas futuras GERADAS por este horário que ainda estão livres
            Aula::where('status', 'disponivel')
                ->where('data_hora_inicio', '>', now())
                // Lógica para encontrar as aulas geradas por este slot (exemplo simplificado)
                // O ideal seria ter uma FK `horario_agenda_id` na tabela `aulas`
                ->whereRaw('EXTRACT(ISODOW FROM data_hora_inicio) = ?', [$horarioAgenda->dia_semana])
                ->whereRaw('TIME(data_hora_inicio) = ?', [$horarioAgenda->horario_inicio])
                ->update(['status' => 'cancelada']);
        }

        $horarioAgenda->update($dadosValidados);

        return response()->json($horarioAgenda);
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
