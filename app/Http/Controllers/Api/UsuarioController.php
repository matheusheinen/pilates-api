<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // <--- CERTIFIQUE-SE DE QUE ESTÁ IMPORTADO
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
        $dadosValidados = $request->validated();

        // Define padrão aluno se não vier
        if (!isset($dadosValidados['tipo'])) {
            $dadosValidados['tipo'] = 'aluno';
        }

        // CORREÇÃO: Criptografar a senha antes de salvar
        $dadosValidados['senha'] = Hash::make($dadosValidados['senha']);

        $usuario = Usuario::create($dadosValidados);
        return response()->json($usuario, 201);
    }

    public function show($id)
    {
        return Usuario::findOrFail($id);
    }

    public function update(UpdateUsuarioRequest $request, $id)
    {
        $usuario = Usuario::findOrFail($id);
        $dadosValidados = $request->validated();

        // CORREÇÃO: Lógica Inteligente da Senha
        // Verifica se o campo 'senha' foi enviado e não está vazio/nulo
        if (!empty($dadosValidados['senha'])) {
            // Se veio senha nova, criptografa
            $dadosValidados['senha'] = Hash::make($dadosValidados['senha']);
        } else {
            // Se não veio (ou veio null), remove do array para não sobrescrever a senha antiga com vazio
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
}
