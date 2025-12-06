<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use App\Models\Aula;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
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
        DB::beginTransaction();

        try {
            $dadosValidados = $request->validated();

            if (!isset($dadosValidados['tipo'])) {
                $dadosValidados['tipo'] = 'aluno';
            }

            $dadosValidados['senha'] = Hash::make($dadosValidados['senha']);

            $usuario = Usuario::create($dadosValidados);

            $aulaInteresse = $request->input('aula_interesse');

            if ($aulaInteresse && is_array($aulaInteresse)) {

                Aula::create([
                    'usuario_id' => $usuario->id,
                    'horario_agenda_id' => $aulaInteresse['horario_agenda_id'],
                    'data_hora_inicio' => $aulaInteresse['data_hora'],
                    'status' => 'agendada',
                    'duracao_minutos' => 50,
                    'observacoes' => 'Primeira aula (Agendada no Cadastro)',
                ]);
            }

            DB::commit();

            return response()->json($usuario, 201);

        } catch (\Exception $e) {
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
