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


class MensalidadeController extends Controller
{
    /**
     * Lista as mensalidades com filtros.
     */
    public function index(Request $request)
    {
        // --- AUTO-ATUALIZAÇÃO DE STATUS (NOVO) ---
        // Antes de listar, verifica se existem mensalidades 'pendente' vencidas
        // e atualiza para 'atrasada'.
        Mensalidade::where('status', 'pendente')
            ->where('data_vencimento', '<', Carbon::now()->format('Y-m-d'))
            ->update(['status' => 'atrasada']);
        // ------------------------------------------

        $query = Mensalidade::with(['inscricao.usuario', 'inscricao.plano', 'pagamento']);

        // Filtro de Segurança para Aluno
        $user = Auth::user(); // ou Auth::user() se preferir
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

    /**
     * Gera mensalidade manualmente (avulsa).
     */
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

    /**
     * [ALUNO] Envia o comprovante de pagamento.
     */
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

    /**
     * [ADMIN] Registro Manual (Baixa direta).
     * Gera a próxima mensalidade e aulas automaticamente.
     */
    public function registrarPagamento(Request $request, $id)
    {
        $request->validate([
            'data_pagamento' => 'required|date',
            'valor_pago' => 'required|numeric',
            'metodo_pagamento' => 'required|string',
        ]);

        // Carrega a inscrição para a lógica de renovação
        $mensalidade = Mensalidade::with('inscricao.plano')->findOrFail($id);

        if ($mensalidade->status === 'paga') {
            return response()->json(['message' => 'Esta mensalidade já está paga.'], 422);
        }

        try {
            DB::transaction(function () use ($mensalidade, $request) {
                // 1. Registra ou atualiza o pagamento
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

                // 2. Atualiza a Mensalidade
                $mensalidade->update(['status' => 'paga']);

                // 3. Renova
                $this->processarRenovacao($mensalidade);
            });

            return response()->json($mensalidade->load('pagamento'), 200);

        } catch (\Exception $e) {
            Log::error("Erro ao registrar pagamento manual ID {$id}: " . $e->getMessage());
            return response()->json(['message' => 'Erro ao registrar pagamento.'], 500);
        }
    }

    /**
     * [ADMIN] Aprova um pagamento em análise.
     */
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

    /**
     * [ADMIN] Rejeita um pagamento.
     */
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

    /**
     * Gera mensalidades em massa para o mês atual.
     */
    public function gerarMassivo()
    {
        $inscricoesAtivas = Inscricao::where('status', 'ativa')->with('plano')->get();
        $hoje = Carbon::now();
        $totalGerado = 0;

        foreach ($inscricoesAtivas as $inscricao) {
            // Sempre define o vencimento para o dia 10 do mês ATUAL da execução.
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

    /**
     * Lógica auxiliar para gerar a próxima mensalidade e liberar aulas.
     */
    private function processarRenovacao(Mensalidade $mensalidadePaga)
    {
        // Recarrega a inscrição se não estiver carregada
        if (!$mensalidadePaga->relationLoaded('inscricao')) {
            $mensalidadePaga->load('inscricao.plano');
        }

        $inscricao = $mensalidadePaga->inscricao;

        if (!$inscricao || $inscricao->status !== 'ativa') {
            Log::warning("Renovação abortada: Inscrição inativa ou inexistente.");
            return;
        }

        // 1. Data da PRÓXIMA mensalidade
        $vencimentoAtual = Carbon::parse($mensalidadePaga->data_vencimento);
        $proximoVencimento = $vencimentoAtual->copy()->addMonth();

        // 2. Verificar se já existe
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

        // 3. Gerar aulas até a data do PRÓXIMO vencimento
        $inscricao->gerarAulasFuturas($proximoVencimento);
    }
}
