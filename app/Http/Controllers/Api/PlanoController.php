<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plano;
use Illuminate\Http\Request;
use App\Http\Requests\StorePlanoRequest;
use App\Http\Requests\UpdatePlanoRequest;

class PlanoController extends Controller
{

    public function index()
    {
        $planos = Plano::all();
        return response()->json($planos);
    }

    public function store(StorePlanoRequest $request)
    {
        $plano = Plano::create($request->validated());
        return response()->json($plano, 201);
    }

    public function show(Plano $plano)
    {
        return response()->json($plano);
    }

    public function update(UpdatePlanoRequest $request, Plano $plano)
    {
        $plano->update($request->validated());
        return response()->json($plano);
    }

    public function destroy(Plano $plano)
    {
        $plano->delete();
        return response()->json(null, 204);
    }
}
