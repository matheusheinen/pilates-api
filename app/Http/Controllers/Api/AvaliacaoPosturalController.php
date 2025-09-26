<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAvaliacaoPosturalRequest;
use App\Http\Requests\UpdateAvaliacaoPosturalRequest;
use App\Models\AvaliacaoPostural;
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
    public function show($id)
    {
        return AvaliacaoPostural::findOrFail($id);
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
}
