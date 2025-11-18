<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAvaliacaoPosturalRequest;
use App\Http\Requests\UpdateAvaliacaoPosturalRequest;
use App\Models\AvaliacaoPostural;
use App\Models\Usuario;
use Illuminate\Http\Request;

class AvaliacaoPosturalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return AvaliacaoPostural::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAvaliacaoPosturalRequest $request)
    {
        $dadosValidados = $request->validated();

        $avaliacaoPostural = AvaliacaoPostural::create($dadosValidados);
        return response()->json($avaliacaoPostural, 201);
    }

    /**
     * Display the specified resource.
     */
    /**
     * Display the specified resource.
     */
    public function show(AvaliacaoPostural $avaliacaoPostural)
    {
        // --- INÍCIO DA CORREÇÃO ---
        // Verifica se o frontend pediu ?with=usuario na URL
        if (request()->has('with') && request('with') == 'usuario') {
            // Se sim, carrega a relação 'usuario' que existe no Modelo
            $avaliacaoPostural->load('usuario');
        }
        // --- FIM DA CORREÇÃO ---

        return response()->json([
            'data' => $avaliacaoPostural
        ]);
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAvaliacaoPosturalRequest $request, $id)
    {
        $dadosValidados = $request->validated();

        $avaliacaoPostural = AvaliacaoPostural::findOrFail($id);
        $avaliacaoPostural->update($dadosValidados);
        return response()->json($avaliacaoPostural, 200);
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AvaliacaoPostural $avaliacaoPostural)
    {
        $avaliacaoPostural->delete();
        return response()->json(null, 204);
    }


    public function getAvaliacoesPorUsuario($id)
    {
        // Encontra o usuário ou falha
        $usuario = Usuario::findOrFail($id);

        // Busca as avaliações usando a relação e ordena pela mais recente
        $avaliacoes = $usuario->avaliacoesPosturais()
                              ->orderBy('data_avaliacao', 'desc')
                              ->get();

        return response()->json([
            'data' => $avaliacoes
        ]);
    }
}
