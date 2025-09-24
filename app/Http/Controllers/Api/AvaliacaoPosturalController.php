<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
    public function store(Request $request)
    {
        $avaliacaoPostural = AvaliacaoPostural::create($request->all());
        return response()->json($avaliacaoPostural, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return AvaliacaoPostural::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $avaliacaoPostural = AvaliacaoPostural::findOrFail($id);
        $avaliacaoPostural->update($request->all());
        return response()->json($avaliacaoPostural, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        AvaliacaoPostural::destroy($id);
        return response()->json(null, 204);
    }
}
