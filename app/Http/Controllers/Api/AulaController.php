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
        // 1. Inicia a consulta na tabela 'aulas'
        $query = Aula::query();

        // Pega o usuário que está autenticado
        $usuarioLogado = Auth::user();

        // 2. LÓGICA DE PERMISSÃO: Verifica o tipo de usuário
        if ($usuarioLogado->tipo === 'aluno') {
            // Se for um ALUNO, força a consulta a trazer apenas as suas próprias aulas.
            $query->where('usuario_id', $usuarioLogado->id);
        } else if ($usuarioLogado->tipo === 'admin') {
            // Se for um ADMIN, ele pode filtrar por um aluno específico.
            // Verifica se um 'usuario_id' foi passado como parâmetro na URL.
            if ($request->has('usuario_id')) {
                $query->where('usuario_id', $request->usuario_id);
            }
        }

        // 3. LÓGICA DE FILTRO POR DATA:
        // Por padrão, busca as aulas do mês atual.
        $mes = $request->input('mes', Carbon::now()->format('Y-m'));
        
        try {
            $dataInicio = Carbon::createFromFormat('Y-m', $mes)->startOfMonth();
            $dataFim = Carbon::createFromFormat('Y-m', $mes)->endOfMonth();
        } catch (\Exception $e) {
            // Se o formato do mês for inválido, retorna um erro.
            return response()->json(['message' => 'Formato de mês inválido. Use YYYY-MM.'], 400);
        }

        // Adiciona o filtro de data à consulta
        $query->whereBetween('data_aula', [$dataInicio, $dataFim]);

        // 4. Carrega os dados do aluno junto com cada aula para uma resposta mais completa.
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
        // 1. Encontra a aula pelo ID ou falha com um erro 404.
        $aula = Aula::findOrFail($id);

        // 2. Lógica de Permissão: Garante que um aluno só pode ver as suas próprias aulas.
        $usuarioLogado = Auth::user();
        if ($usuarioLogado->tipo === 'aluno' && $aula->usuario_id !== $usuarioLogado->id) {
            // Se o aluno não for o "dono" da aula, retorna um erro de acesso negado.
            return response()->json(['message' => 'Acesso não autorizado.'], 403); // 403 Forbidden
        }

        // 3. Carrega os dados do aluno junto com a aula para uma resposta mais completa.
        $aula->load('usuario');

        return response()->json($aula);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAulaRequest $request, string $id)
    {
        // 1. Encontra a aula ou falha com um erro 404.
        $aula = Aula::findOrFail($id);
        $dadosValidados = $request->validated();
        $usuarioLogado = Auth::user();

        // --- VERIFICAÇÃO DAS REGRAS DE NEGÓCIO PARA REAGENDAMENTO ---
        // Estas regras só se aplicam se um aluno estiver a tentar mudar a data/hora.
        if ($usuarioLogado->tipo === 'aluno' && (isset($dadosValidados['data_aula']) || isset($dadosValidados['horario']))) {

            // 2. REGRA: Antecedência de 24 horas
            $horarioOriginalDaAula = Carbon::parse($aula->data_aula . ' ' . $aula->horario);
            if (Carbon::now()->diffInHours($horarioOriginalDaAula) < 24) {
                return response()->json([
                    'message' => 'O reagendamento só pode ser feito com mais de 24 horas de antecedência.'
                ], 403); // 403 Forbidden
            }

            // 3. REGRA: Verificar vagas no novo horário (limite de 3 alunos)
            $novaData = $dadosValidados['data_aula'] ?? $aula->data_aula;
            $novoHorario = $dadosValidados['horario'] ?? $aula->horario;

            $alunosNoNovoHorario = Aula::where('data_aula', $novaData)
                                       ->where('horario', $novoHorario)
                                       ->count();

            if ($alunosNoNovoHorario >= 3) {
                return response()->json(['message' => 'O novo horário escolhido já está cheio.'], 409); // 409 Conflict
            }
        }
        // --- FIM DA VERIFICAÇÃO ---

        // 4. Se todas as regras passaram, atualiza a aula.
        $aula->update($dadosValidados);

        // Se foi um reagendamento, é uma boa prática mudar o tipo para 'reposicao'.
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
