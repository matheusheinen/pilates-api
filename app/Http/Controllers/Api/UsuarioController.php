<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StoreUsuarioRequest;
use App\Http\Requests\UpdateUsuarioRequest;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Busca no banco de dados todos os usuários onde a coluna 'tipo' é 'aluno'
        $alunos = Usuario::where('tipo', 'aluno')->get();

        // Retorna a lista de alunos
        return response()->json(['data' => $alunos]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUsuarioRequest $request)
    {
        $dadosValidados = $request->validated();

        if($dadosValidados['tipo'] == null) {
            $dadosValidados['tipo'] = 'aluno';
        }

        $usuario = Usuario::create($dadosValidados);
        return response()->json($usuario, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return Usuario::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUsuarioRequest $request, $id)
    {
        $dadosValidados = $request->validated();


        $usuario = Usuario::findOrFail($id);
        $usuario->update($dadosValidados);
        return response()->json($usuario, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Usuario $usuario)
    {
        $usuario->delete();
        return response()->json(null, 204);
    }
}
