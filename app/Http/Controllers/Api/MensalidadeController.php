<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mensalidade;
use App\Models\Inscricao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;


class MensalidadeController extends Controller
{

    public function index(Request $request)
    {
        Mensalidade::where('status', 'pendente')
            ->where('data_vencimento', '<', Carbon::now()->format('Y-m-d'))
            ->update(['status' => 'atrasada']);
        // ------------------------------------------

        $query = Mensalidade::with(['inscricao.usuario', 'inscricao.plano', 'pagamento']);

        $user = Auth::user();
        if ($user && $user->tipo === 'aluno') {
             $query->whereHas('inscricao', function($q) use ($user) {
                $q->where('usuario_id', $user->id);
            });
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('mes') && $request->mes) {
            $query->where('data_vencimento', 'like', $request->mes . '%');
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('inscricao.usuario', function($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%");
            });
        }

        $mensalidades = $query->orderBy('data_vencimento', 'asc')->paginate(15);
        return response()->json($mensalidades);
    }

    public function store(Request $request)
    {
        $request->validate([
            'inscricao_id' => 'required|exists:inscricoes,id',
            'data_vencimento' => 'required|date',
            'valor' => 'required|numeric',
        ]);

        try {
            $mensalidade = Mensalidade::create($request->all());
            return response()->json($mensalidade, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao criar mensalidade: ' . $e->getMessage()], 500);
        }
    }

    public function enviarComprovante(Request $request, $id)
    {
        $request->validate([
            'comprovante' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'data_pagamento' => 'required|date',
        ]);

        $mensalidade = Mensalidade::findOrFail($id);

        if ($mensalidade->status === 'paga') {
            return response()->json(['message' => 'Esta mensalidade já está paga.'], 422);
        }

        try {
            DB::transaction(function () use ($mensalidade, $request) {
                $path = $request->file('comprovante')->store('comprovantes', 'public');

                if ($mensalidade->pagamento) {
                    $mensalidade->pagamento()->delete();
                }

                $mensalidade->pagamento()->create([
                    'data_pagamento' => $request->data_pagamento,
                    'valor_pago' => $mensalidade->valor,
                    'metodo_pagamento' => 'comprovante_anexado',
                    'comprovante_path' => $path,
                    'status' => 'em_analise'
                ]);

                $mensalidade->update(['status' => 'em_analise']);
            });

            return response()->json(['message' => 'Comprovante enviado para análise.']);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao enviar comprovante: ' . $e->getMessage()], 500);
        }
    }


    public function registrarPagamento(Request $request, $id)
    {
        $request->validate([
            'data_pagamento' => 'required|date',
            'valor_pago' => 'required|numeric',
            'metodo_pagamento' => 'required|string',
        ]);

        $mensalidade = Mensalidade::with('inscricao.plano')->findOrFail($id);

        if ($mensalidade->status === 'paga') {
            return response()->json(['message' => 'Esta mensalidade já está paga.'], 422);
        }

        try {
            DB::transaction(function () use ($mensalidade, $request) {
                if ($mensalidade->pagamento) {
                    $mensalidade->pagamento()->update([
                        'data_pagamento' => $request->data_pagamento,
                        'valor_pago' => $request->valor_pago,
                        'metodo_pagamento' => $request->metodo_pagamento,
                        'status' => 'aprovado'
                    ]);
                } else {
                    $mensalidade->pagamento()->create([
                        'data_pagamento' => $request->data_pagamento,
                        'valor_pago' => $request->valor_pago,
                        'metodo_pagamento' => $request->metodo_pagamento,
                        'status' => 'aprovado'
                    ]);
                }

                $mensalidade->update(['status' => 'paga']);

                $this->processarRenovacao($mensalidade);
            });

            return response()->json($mensalidade->load('pagamento'), 200);

        } catch (\Exception $e) {
            Log::error("Erro ao registrar pagamento manual ID {$id}: " . $e->getMessage());
            return response()->json(['message' => 'Erro ao registrar pagamento.'], 500);
        }
    }

    public function aprovarPagamento($id)
    {
        $mensalidade = Mensalidade::with('inscricao.plano')->findOrFail($id);

        if ($mensalidade->status !== 'em_analise') {
            return response()->json(['message' => 'Esta mensalidade não está em análise.'], 422);
        }

        try {
            DB::transaction(function () use ($mensalidade) {
                $mensalidade->update(['status' => 'paga']);
                $mensalidade->pagamento()->update(['status' => 'aprovado']);

                $this->processarRenovacao($mensalidade);
            });

            return response()->json(['message' => 'Pagamento aprovado com sucesso.']);
        } catch (\Exception $e) {
            Log::error("Erro ao aprovar pagamento ID {$id}: " . $e->getMessage());
            return response()->json(['message' => 'Erro ao aprovar pagamento.'], 500);
        }
    }


    public function rejeitarPagamento($id)
    {
        $mensalidade = Mensalidade::findOrFail($id);

        if ($mensalidade->status !== 'em_analise') {
            return response()->json(['message' => 'Esta mensalidade não está em análise.'], 422);
        }

        try {
            DB::transaction(function () use ($mensalidade) {
                $mensalidade->update(['status' => 'pendente']);
                $mensalidade->pagamento()->update(['status' => 'rejeitado']);
            });

            return response()->json(['message' => 'Pagamento rejeitado.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao rejeitar.'], 500);
        }
    }


    public function gerarMassivo()
    {
        $inscricoesAtivas = Inscricao::where('status', 'ativa')->with('plano')->get();
        $hoje = Carbon::now();
        $totalGerado = 0;

        foreach ($inscricoesAtivas as $inscricao) {

            $vencimento = Carbon::create($hoje->year, $hoje->month, 10);

            $existe = Mensalidade::where('inscricao_id', $inscricao->id)
                ->whereMonth('data_vencimento', $vencimento->month)
                ->whereYear('data_vencimento', $vencimento->year)
                ->exists();

            if (!$existe) {
                Mensalidade::create([
                    'inscricao_id' => $inscricao->id,
                    'data_vencimento' => $vencimento->format('Y-m-d'),
                    'valor' => $inscricao->plano->preco,
                    'status' => 'pendente'
                ]);
                $totalGerado++;
            }
        }

        return response()->json(['message' => "{$totalGerado} mensalidades geradas para este mês."]);
    }


    private function processarRenovacao(Mensalidade $mensalidadePaga)
    {

        if (!$mensalidadePaga->relationLoaded('inscricao')) {
            $mensalidadePaga->load('inscricao.plano');
        }

        $inscricao = $mensalidadePaga->inscricao;

        if (!$inscricao || $inscricao->status !== 'ativa') {
            Log::warning("Renovação abortada: Inscrição inativa ou inexistente.");
            return;
        }

        $vencimentoAtual = Carbon::parse($mensalidadePaga->data_vencimento);
        $proximoVencimento = $vencimentoAtual->copy()->addMonth();


        $existeProxima = Mensalidade::where('inscricao_id', $inscricao->id)
            ->whereMonth('data_vencimento', $proximoVencimento->month)
            ->whereYear('data_vencimento', $proximoVencimento->year)
            ->exists();

        if (!$existeProxima) {
            Mensalidade::create([
                'inscricao_id' => $inscricao->id,
                'data_vencimento' => $proximoVencimento->format('Y-m-d'),
                'valor' => $inscricao->plano->preco,
                'status' => 'pendente'
            ]);
            Log::info("Nova mensalidade gerada para {$proximoVencimento->format('Y-m-d')}");
        }


        $inscricao->gerarAulasFuturas($proximoVencimento);
    }


    public function visualizarComprovante($id)
    {
        $mensalidade = Mensalidade::with('pagamento')->findOrFail($id);

        if (!$mensalidade->pagamento || !$mensalidade->pagamento->comprovante_path) {
            abort(404, 'Comprovante não encontrado.');
        }

        $path = $mensalidade->pagamento->comprovante_path;

        if (!Storage::disk('public')->exists($path)) {
            abort(404, 'Arquivo físico não encontrado.');
        }

        return response()->file(Storage::disk('public')->path($path));
    }
}
