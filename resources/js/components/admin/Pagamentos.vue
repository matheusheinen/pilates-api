<template>
  <div class="bg-[#151515] p-6 rounded-xl text-white">

    <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-6 gap-4">
      <div>
        <h2 class="text-lg font-semibold">Histórico Financeiro</h2>
        <p v-if="nomeCliente" class="text-teal-400 text-sm mt-1">Cliente: {{ nomeCliente }}</p>
        <p v-else class="text-[#a0a0a0]">Carregando dados...</p>
      </div>

      <router-link
        v-if="id"
        :to="{ name: 'detalhes-cliente', params: { id: id } }"
        class="text-gray-400 hover:text-white text-sm font-semibold flex items-center gap-1 transition-colors bg-[#242424] px-3 py-2 rounded-lg border border-gray-700 hover:border-gray-500"
      >
        &larr; Voltar aos Detalhes
      </router-link>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-[#1e1e1e] p-4 rounded-xl border border-[#333] relative overflow-hidden">
            <div class="absolute right-0 top-0 p-4 opacity-10 text-green-500"><svg width="40" height="40" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm.31-8.86c-1.77-.45-2.34-.94-2.34-1.67 0-.84.79-1.43 2.1-1.43 1.38 0 1.9.66 1.94 1.64h1.71c-.05-1.34-.87-2.57-2.49-2.97V5H10.9v1.69c-1.51.32-2.72 1.3-2.72 2.81 0 1.79 1.49 2.69 3.66 3.21 1.95.46 2.34 1.15 2.34 1.87 0 .53-.39 1.39-2.1 1.39-1.6 0-2.23-.72-2.32-1.64H8.04c.1 1.7 1.36 2.66 2.86 2.97V19h2.34v-1.67c1.52-.29 2.72-1.16 2.73-2.77-.01-2.2-1.9-2.96-3.66-3.42z"/></svg></div>
            <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">Total Pago</p>
            <p class="text-2xl text-green-500 font-mono font-bold mt-1">R$ {{ formatarPreco(totalPago) }}</p>
        </div>
        <div class="bg-[#1e1e1e] p-4 rounded-xl border border-[#333]">
            <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">Pendente</p>
            <p class="text-2xl text-yellow-500 font-mono font-bold mt-1">R$ {{ formatarPreco(totalPendente) }}</p>
        </div>
        <div class="bg-[#1e1e1e] p-4 rounded-xl border border-[#333]">
            <p class="text-xs text-gray-400 uppercase font-bold tracking-wider">Atrasado</p>
            <p class="text-2xl text-red-500 font-mono font-bold mt-1">R$ {{ formatarPreco(totalAtrasado) }}</p>
        </div>
    </div>

    <div class="bg-[#1e1e1e] rounded-xl border border-[#333] overflow-hidden shadow-sm relative">

      <div v-if="loading" class="absolute inset-0 bg-[#1e1e1e]/80 z-10 flex items-center justify-center">
        <div class="text-teal-500 font-semibold animate-pulse">Carregando histórico...</div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-gray-300">
          <thead class="bg-[#252525] text-gray-400 uppercase text-xs">
            <tr>
              <th class="px-6 py-3 font-medium tracking-wider">Vencimento</th>
              <th class="px-6 py-3 font-medium tracking-wider">Valor</th>
              <th class="px-6 py-3 font-medium tracking-wider">Status</th>
              <th class="px-6 py-3 font-medium tracking-wider">Pagamento</th>
              <th class="px-6 py-3 font-medium tracking-wider text-right">Ações</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#333]">

            <tr v-if="!loading && mensalidades.length === 0">
                <td colspan="5" class="px-6 py-8 text-center text-gray-500 italic">
                    Nenhum registro financeiro encontrado.
                </td>
            </tr>

            <tr v-for="fatura in mensalidades" :key="fatura.id" class="hover:bg-[#2a2a2a] transition duration-150">

              <td class="px-6 py-4 font-medium text-white">
                {{ formatarData(fatura.data_vencimento) }}
              </td>

              <td class="px-6 py-4 font-mono text-teal-400">
                R$ {{ formatarPreco(fatura.valor) }}
              </td>

              <td class="px-6 py-4">
                <span :class="statusClass(fatura.status)" class="px-2 py-1 rounded text-[10px] font-bold uppercase border border-white/5 whitespace-nowrap">
                  {{ fatura.status.replace('_', ' ') }}
                </span>
              </td>

              <td class="px-6 py-4 text-sm">
                 <div v-if="fatura.pagamento" class="flex flex-col">
                     <span class="text-white">{{ formatarData(fatura.pagamento.data_pagamento) }}</span>
                     <span class="text-xs text-gray-500 capitalize">{{ fatura.pagamento.metodo_pagamento.replace('_', ' ') }}</span>
                 </div>
                 <span v-else class="text-gray-600">-</span>
              </td>

              <td class="px-6 py-4 text-right">

                <div v-if="fatura.status === 'em_analise'" class="flex justify-end gap-2">
                    <button @click="verComprovante(fatura.pagamento?.comprovante_path)" class="text-blue-400 hover:text-blue-300 text-xs underline mr-2">Ver Anexo</button>
                    <button @click="aprovar(fatura.id)" class="bg-green-600/20 hover:bg-green-600 text-green-400 hover:text-white border border-green-600/50 p-1.5 rounded transition" title="Aprovar">✓</button>
                    <button @click="rejeitar(fatura.id)" class="bg-red-600/20 hover:bg-red-600 text-red-400 hover:text-white border border-red-600/50 p-1.5 rounded transition" title="Rejeitar">✕</button>
                </div>

                <button v-else-if="['pendente', 'atrasada'].includes(fatura.status)"
                        @click="abrirModalPagamento(fatura)"
                        class="text-teal-500 hover:text-teal-400 text-xs font-semibold border border-teal-500/30 px-3 py-1.5 rounded hover:bg-teal-500/10 transition">
                  Baixa Manual
                </button>

                <button v-else-if="fatura.pagamento?.comprovante_path"
                        @click="verComprovante(fatura.pagamento.comprovante_path)"
                        class="text-blue-500 hover:text-blue-400 text-xs underline">
                    Ver Comprovante
                </button>
                <span v-else class="text-gray-600 text-xs">-</span>

              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="px-6 py-4 border-t border-[#333] flex justify-between items-center" v-if="paginacao.total > 0">
          <span class="text-xs text-gray-500">Mostrando {{ paginacao.from }} - {{ paginacao.to }} de {{ paginacao.total }}</span>
          <div class="flex gap-2">
              <button @click="mudarPagina(paginacao.current_page - 1)" :disabled="paginacao.current_page === 1" class="px-3 py-1 bg-[#252525] hover:bg-[#333] border border-[#333] rounded text-sm disabled:opacity-50 transition">&lt;</button>
              <button @click="mudarPagina(paginacao.current_page + 1)" :disabled="paginacao.current_page === paginacao.last_page" class="px-3 py-1 bg-[#252525] hover:bg-[#333] border border-[#333] rounded text-sm disabled:opacity-50 transition">&gt;</button>
          </div>
      </div>
    </div>

    <div v-if="modalAberto" class="fixed inset-0 bg-black/80 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-[#1f1f1f] rounded-xl border border-gray-600 w-full max-w-md p-6 shadow-2xl">
        <h3 class="text-xl font-bold text-white mb-4 border-b border-gray-700 pb-3">Baixa Manual</h3>

        <div class="mb-4 p-3 bg-[#151515] rounded border border-gray-700">
            <p class="text-sm text-gray-400">Vencimento: <span class="text-white">{{ formatarData(mensalidadeSelecionada?.data_vencimento) }}</span></p>
            <p class="text-sm text-gray-400 mt-1">Valor: <span class="text-teal-400">R$ {{ formatarPreco(mensalidadeSelecionada?.valor) }}</span></p>
        </div>

        <form @submit.prevent="confirmarPagamentoManual" class="space-y-4">
          <div>
            <label class="block text-xs text-gray-400 mb-1">Data do Recebimento</label>
            <input v-model="formPagamento.data_pagamento" type="date" class="form-input-style" required />
          </div>
          <div>
            <label class="block text-xs text-gray-400 mb-1">Valor Recebido (R$)</label>
            <input v-model="formPagamento.valor_pago" type="number" step="0.01" class="form-input-style" required />
          </div>
          <div>
            <label class="block text-xs text-gray-400 mb-1">Método</label>
            <select v-model="formPagamento.metodo_pagamento" class="form-input-style" required>
                <option value="pix">PIX</option>
                <option value="dinheiro">Dinheiro</option>
                <option value="cartao_credito">Cartão de Crédito</option>
                <option value="cartao_debito">Cartão de Débito</option>
            </select>
          </div>
          <div class="flex justify-end gap-3 mt-6">
            <button type="button" @click="fecharModal" class="px-4 py-2 text-gray-400 hover:text-white text-sm transition">Cancelar</button>
            <button type="submit" :disabled="salvandoPagamento" class="bg-teal-600 hover:bg-teal-500 text-white px-6 py-2 rounded text-sm font-semibold disabled:opacity-50 transition">
                {{ salvandoPagamento ? 'Salvando...' : 'Confirmar' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed, reactive } from 'vue';
import axios from 'axios';

const props = defineProps({
    id: { type: [String, Number], required: true }
});

const mensalidades = ref([]);
const paginacao = ref({});
const loading = ref(true);
const modalAberto = ref(false);
const mensalidadeSelecionada = ref(null);
const salvandoPagamento = ref(false);

const formPagamento = reactive({
    data_pagamento: '',
    valor_pago: '',
    metodo_pagamento: 'pix'
});

const nomeCliente = computed(() => mensalidades.value.length > 0 ? mensalidades.value[0].inscricao?.usuario?.nome : '');
const totalPago = computed(() => mensalidades.value.filter(m => m.status === 'paga').reduce((acc, m) => acc + Number(m.valor), 0));
const totalPendente = computed(() => mensalidades.value.filter(m => m.status === 'pendente' || m.status === 'em_analise').reduce((acc, m) => acc + Number(m.valor), 0));
const totalAtrasado = computed(() => mensalidades.value.filter(m => m.status === 'atrasada').reduce((acc, m) => acc + Number(m.valor), 0));

const fetchHistorico = async (page = 1) => {
    loading.value = true;
    try {
        const response = await axios.get('/api/mensalidades', {
            params: { usuario_id: props.id, page: page }
        });
        mensalidades.value = response.data.data;
        paginacao.value = response.data;
    } catch (error) {
        console.error("Erro ao buscar histórico:", error);
    } finally {
        loading.value = false;
    }
};

const mudarPagina = (page) => { if (page >= 1 && page <= paginacao.value.last_page) fetchHistorico(page); };

const aprovar = async (id) => {
    if(!confirm('Confirmar pagamento?')) return;
    try { await axios.post(`/api/mensalidades/${id}/aprovar`); fetchHistorico(paginacao.value.current_page); } catch(e) { alert('Erro.'); }
};
const rejeitar = async (id) => {
    if(!confirm('Rejeitar comprovante?')) return;
    try { await axios.post(`/api/mensalidades/${id}/rejeitar`); fetchHistorico(paginacao.value.current_page); } catch(e) { alert('Erro.'); }
};

const abrirModalPagamento = (mensalidade) => {
    mensalidadeSelecionada.value = mensalidade;
    const hoje = new Date();
    const offset = hoje.getTimezoneOffset();
    const dataLocal = new Date(hoje.getTime() - (offset * 60 * 1000)).toISOString().split('T')[0];

    formPagamento.data_pagamento = dataLocal;
    formPagamento.valor_pago = mensalidade.valor;
    formPagamento.metodo_pagamento = 'pix';
    modalAberto.value = true;
};

const confirmarPagamentoManual = async () => {
    if (!mensalidadeSelecionada.value) return;
    salvandoPagamento.value = true;
    try {
        await axios.post(`/api/mensalidades/${mensalidadeSelecionada.value.id}/pagar`, formPagamento);
        fetchHistorico(paginacao.value.current_page);
        fecharModal();
    } catch (error) {
        alert('Erro: ' + (error.response?.data?.message || 'Falha ao salvar.'));
    } finally {
        salvandoPagamento.value = false;
    }
};

const fecharModal = () => { modalAberto.value = false; mensalidadeSelecionada.value = null; };
const verComprovante = (path) => { window.open(`/storage/${path}`, '_blank'); };

const formatarData = (d) => d ? new Date(d).toLocaleDateString('pt-BR', { timeZone: 'UTC' }) : '-';
const formatarPreco = (v) => parseFloat(v).toFixed(2).replace('.', ',');
const statusClass = (s) => {
    const map = {
        'pendente': 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
        'em_analise': 'bg-blue-500/10 text-blue-400 border-blue-500/30',
        'paga': 'bg-green-500/10 text-green-400 border-green-500/30',
        'atrasada': 'bg-red-500/10 text-red-400 border-red-500/30',
        'cancelada': 'bg-gray-500/10 text-gray-400 border-gray-500/30',
    };
    return map[s] || 'bg-gray-700 text-gray-300';
};

onMounted(() => { if (props.id) fetchHistorico(); });
</script>

<style scoped>
.form-input-style {
  @apply w-full p-2.5 rounded-lg bg-[#242424] text-white border border-[#333] focus:border-teal-500 focus:ring-1 focus:ring-teal-500 focus:outline-none text-sm transition-all;
}
</style>
