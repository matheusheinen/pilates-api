<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Aula; // <--- Importante: Importar o Model Aula
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB; // <--- Importante: Para usar transações
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;

class UsuarioController extends Controller
{
    public function index()
    {
        $alunos = Usuario::where('tipo', 'aluno')->get();
        return response()->json(['data' => $alunos]);
    }

    public function store(StoreUsuarioRequest $request)
    {
        // Inicia uma transação: ou salva tudo (usuário + aula) ou não salva nada
        DB::beginTransaction();

        try {
            $dadosValidados = $request->validated();

            if (!isset($dadosValidados['tipo'])) {
                $dadosValidados['tipo'] = 'aluno';
            }

            $dadosValidados['senha'] = Hash::make($dadosValidados['senha']);

            // 1. Cria o Usuário
            $usuario = Usuario::create($dadosValidados);

            // 2. Verifica se veio o pedido de agendamento (aula_interesse)
            // Nota: Usamos $request->input porque 'aula_interesse' provavelmente não está no rules() do StoreUsuarioRequest
            $aulaInteresse = $request->input('aula_interesse');

            if ($aulaInteresse && is_array($aulaInteresse)) {

                // Cria a aula experimental/inicial
                Aula::create([
                    'usuario_id' => $usuario->id,
                    'horario_agenda_id' => $aulaInteresse['horario_agenda_id'],
                    'data_hora_inicio' => $aulaInteresse['data_hora'], // Formato ISO enviado pelo front
                    'status' => 'agendada',
                    'duracao_minutos' => 50, // Padrão ou buscar do HorarioAgenda
                    'observacoes' => 'Primeira aula (Agendada no Cadastro)',
                    // 'inscricao_id' => null // Aula avulsa/experimental geralmente não tem inscrição ainda
                ]);
            }

            // Confirma as alterações no banco
            DB::commit();

            return response()->json($usuario, 201);

        } catch (\Exception $e) {
            // Se der erro, desfaz tudo
            DB::rollback();
            Log::error("Erro ao cadastrar usuário: " . $e->getMessage());

            return response()->json([
                'message' => 'Erro ao realizar cadastro.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        return Usuario::findOrFail($id);
    }

    public function update(UpdateUsuarioRequest $request, $id)
    {
        $usuario = Usuario::findOrFail($id);
        $dadosValidados = $request->validated();

        if (!empty($dadosValidados['senha'])) {
            $dadosValidados['senha'] = Hash::make($dadosValidados['senha']);
        } else {
            unset($dadosValidados['senha']);
        }

        $usuario->update($dadosValidados);

        return response()->json($usuario, 200);
    }

    public function destroy(Usuario $usuario)
    {
        $usuario->delete();
        return response()->json(null, 204);
    }

    public function avaliacoesPosturais($id)
    {
        $usuario = Usuario::findOrFail($id);
        $avaliacoes = $usuario->avaliacoesPosturais()
                            ->orderBy('data_avaliacao', 'desc')
                            ->get();

        return response()->json(['data' => $avaliacoes]);
    }
}
