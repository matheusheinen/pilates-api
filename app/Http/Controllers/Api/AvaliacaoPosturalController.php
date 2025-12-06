<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAvaliacaoPosturalRequest;
use App\Http\Requests\UpdateAvaliacaoPosturalRequest;
use App\Models\AvaliacaoPostural;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AvaliacaoPosturalController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = AvaliacaoPostural::query();

            if ($request->has('usuario_id')) {
                $query->where('usuario_id', $request->usuario_id);
            }

            if ($request->has('latest') && $request->latest === 'true') {
                $query->latest('data_avaliacao')->first();
                return response()->json($query->latest('data_avaliacao')->first());
            }

            $avaliacoes = $query->latest('data_avaliacao')->get();
            return response()->json($avaliacoes);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Erro ao listar avaliações: " . $e->getMessage());
            return response()->json([
                'message' => 'Erro ao listar avaliações.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $avaliacao = AvaliacaoPostural::with('usuario')->findOrFail($id);
            return response()->json($avaliacao);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Avaliação não encontrada.'], 404);
        }
    }


    public function store(StoreAvaliacaoPosturalRequest $request)
    {
        try {

            $dados = $request->validated();


            if ($request->hasFile('anexo')) {
                $dados['caminho_anexo'] = $request->file('anexo')->store('anexos_posturais', 'public');
            }


            unset($dados['anexo']);


            $avaliacao = AvaliacaoPostural::create($dados);

            return response()->json($avaliacao, 201);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Erro Avaliação: " . $e->getMessage());

            return response()->json([
                'message' => 'Erro ao salvar avaliação.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(UpdateAvaliacaoPosturalRequest $request, $id)
    {
        try {
            $avaliacao = AvaliacaoPostural::findOrFail($id);
            $dados = $request->validated();

            if ($request->has('remover_anexo') && $avaliacao->caminho_anexo) {
                Storage::disk('public')->delete($avaliacao->caminho_anexo);
                $dados['caminho_anexo'] = null;
            }

            if ($request->hasFile('anexo')) {
                if ($avaliacao->caminho_anexo) {
                    Storage::disk('public')->delete($avaliacao->caminho_anexo);
                }
                $dados['caminho_anexo'] = $request->file('anexo')->store('anexos_posturais', 'public');
            }

            unset($dados['anexo']);

            $avaliacao->update($dados);

            return response()->json($avaliacao, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao atualizar.', 'error' => $e->getMessage()], 500);
        }
    }

    public function visualizarAnexo($id)
    {
        $avaliacao = AvaliacaoPostural::findOrFail($id);
        if (!$avaliacao->caminho_anexo || !Storage::disk('public')->exists($avaliacao->caminho_anexo)) {
            abort(404);
        }
        $filePath = Storage::disk('public')->path($avaliacao->caminho_anexo);
        return response()->file($filePath);
    }
}
