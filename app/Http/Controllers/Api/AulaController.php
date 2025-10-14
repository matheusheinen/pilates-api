<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Aula;
use Illuminate\Support\Facades\Artisan;
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
            // Encontra a inscrição ativa do aluno logado
            $inscricao = Inscricao::where('usuario_id', $usuarioLogado->id)->where('status', 'ativa')->first();
            if ($inscricao) {
                $query->where('inscricao_id', $inscricao->id);
            } else {
                // Se o aluno não tem inscrição ativa, não retorna nenhuma aula
                return response()->json([]);
            }
        }

        // Filtra por mês, se o parâmetro 'mes' for enviado (formato YYYY-MM)
        if ($request->has('mes')) {
            $mes = $request->input('mes');
            $dataInicio = Carbon::createFromFormat('Y-m', $mes)->startOfMonth();
            $dataFim = Carbon::createFromFormat('Y-m', $mes)->endOfMonth();
            $query->whereBetween('data_hora_inicio', [$dataInicio, $dataFim]);
        }

        $aulas = $query->with('inscricao.usuario')->orderBy('data_hora_inicio')->get();

        return response()->json($aulas);
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

    // ...
    public function atualizarAgenda(Request $request)
    {
        // 1. Garante que apenas o admin pode fazer isso
        if (Auth::user()->tipo !== 'admin') {
            return response()->json(['message' => 'Acesso não autorizado'], 403);
        }

        // 2. Define os parâmetros para o comando
        // Por padrão, gera/atualiza as próximas 4 semanas a partir de hoje
        $parametros = [
            '--weeks' => 4
        ];

        // 3. Chama o comando do Artisan 'app:atualizar-agenda'
        Artisan::call('app:atualizar-agenda', $parametros);

        // 4. Pega na saída do comando para devolver uma resposta útil ao frontend
        $output = Artisan::output();

        return response()->json([
            'message' => 'Comando para atualizar a agenda foi executado.',
            'output' => $output
        ]);
    }

}
