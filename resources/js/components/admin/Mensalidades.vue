<template>
  <div class="bg-[#151515] p-6 rounded-xl text-white">

    <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-6 gap-4">
      <div>
        <h3 class="text-lg font-semibold">Gestão de Mensalidades</h3>
        <p class="text-[#a0a0a0]">Controle de pagamentos, vencimentos e comprovantes.</p>
      </div>
    </div>

    <div class="bg-[#1e1e1e] p-4 rounded-xl border border-[#333] mb-6 grid grid-cols-1 md:grid-cols-3 gap-4 items-end shadow-sm">
      <div>
        <label class="text-xs text-gray-400 mb-1 block ml-1">Buscar Aluno</label>
        <input v-model="filtros.search" @input="fetchMensalidades" type="text" placeholder="Nome do aluno..." class="form-input-style" />
      </div>
      <div>
        <label class="text-xs text-gray-400 mb-1 block ml-1">Mês de Vencimento</label>
        <input v-model="filtros.mes" @change="fetchMensalidades" type="month" class="form-input-style" />
      </div>
      <div>
        <label class="text-xs text-gray-400 mb-1 block ml-1">Status</label>
        <select v-model="filtros.status" @change="fetchMensalidades" class="form-input-style">
          <option value="">Todos</option>
          <option value="pendente">Pendente</option>
          <option value="em_analise">Em Análise</option>
          <option value="paga">Paga</option>
          <option value="atrasada">Atrasada</option>
        </select>
      </div>
    </div>

    <div class="bg-[#1e1e1e] rounded-xl border border-[#333] overflow-hidden relative shadow-sm">
      <div v-if="loading" class="absolute inset-0 bg-[#1e1e1e]/80 z-10 flex items-center justify-center">
        <div class="text-teal-500 font-semibold animate-pulse">Carregando...</div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-gray-300">
          <thead class="bg-[#252525] text-gray-400 uppercase text-xs">
            <tr>
              <th class="px-6 py-3 font-medium tracking-wider">Aluno</th>
              <th class="px-6 py-3 font-medium tracking-wider">Plano</th>
              <th class="px-6 py-3 font-medium tracking-wider">Vencimento</th>
              <th class="px-6 py-3 font-medium tracking-wider">Valor</th>
              <th class="px-6 py-3 font-medium tracking-wider">Status</th>
              <th class="px-6 py-3 font-medium tracking-wider text-right">Ações</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#333]">
            <tr v-for="mensalidade in mensalidades" :key="mensalidade.id" class="hover:bg-[#2a2a2a] transition duration-150">
              <td class="px-6 py-4 font-medium text-white">{{ mensalidade.inscricao?.usuario?.nome || 'N/A' }}</td>
              <td class="px-6 py-4 text-sm text-gray-400">{{ mensalidade.inscricao?.plano?.nome || 'N/A' }}</td>
              <td class="px-6 py-4">{{ formatarData(mensalidade.data_vencimento) }}</td>
              <td class="px-6 py-4 font-mono text-teal-400 font-medium">R$ {{ formatarPreco(mensalidade.valor) }}</td>

              <td class="px-6 py-4">
                <span :class="statusClass(mensalidade.status)" class="px-2 py-1 rounded text-[10px] font-bold uppercase whitespace-nowrap border border-white/5">
                  {{ mensalidade.status.replace('_', ' ') }}
                </span>
              </td>

              <td class="px-6 py-4 text-right">

                <div v-if="mensalidade.status === 'em_analise'" class="flex justify-end items-center gap-2">

                    <button
                      v-if="mensalidade.pagamento && mensalidade.pagamento.comprovante_path"
                      @click.stop.prevent="abrirAnexo(mensalidade.id)"
                      class="text-blue-400 hover:text-blue-300 text-xs underline mr-2 flex items-center gap-1 cursor-pointer bg-transparent border-0"
                      type="button"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                        Ver Anexo
                    </button>
                    <span v-else class="text-xs text-gray-600 mr-2 italic">Sem anexo</span>

                    <button @click="aprovar(mensalidade.id)" class="bg-green-600/20 hover:bg-green-600 text-green-400 hover:text-white border border-green-600/50 p-1.5 rounded transition" title="Aprovar">✓</button>
                    <button @click="rejeitar(mensalidade.id)" class="bg-red-600/20 hover:bg-red-600 text-red-400 hover:text-white border border-red-600/50 p-1.5 rounded transition" title="Rejeitar">✕</button>
                </div>

                <button v-else-if="['pendente', 'atrasada'].includes(mensalidade.status)"
                        @click="abrirModalPagamento(mensalidade)"
                        class="text-teal-500 hover:text-teal-400 text-sm font-semibold border border-teal-500/30 px-3 py-1 rounded hover:bg-teal-500/10 transition">
                  Baixa Manual
                </button>

                <div v-else-if="mensalidade.status === 'paga'" class="text-xs text-gray-500 flex flex-col items-end">
                    <span class="text-green-500/70 font-semibold">Pago em {{ formatarData(mensalidade.pagamento?.data_pagamento) }}</span>

                    <button
                      v-if="mensalidade.pagamento && mensalidade.pagamento.comprovante_path"
                      @click.stop.prevent="abrirAnexo(mensalidade.id)"
                      class="text-blue-500 hover:text-blue-400 underline mt-1 flex items-center gap-1 cursor-pointer bg-transparent border-0"
                      type="button"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                        Ver Comprovante
                    </button>
                </div>

              </td>
            </tr>
            <tr v-if="!loading && mensalidades.length === 0">
                <td colspan="6" class="p-8 text-center text-gray-500 italic">Nenhuma mensalidade encontrada.</td>
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
      <div class="bg-[#1f1f1f] rounded-xl border border-gray-600 w-full max-w-md p-6 shadow-2xl transform transition-all">
        <h3 class="text-xl font-bold text-white mb-4 border-b border-gray-700 pb-3">Baixa Manual de Pagamento</h3>
        <div class="mb-6 p-4 bg-[#151515] rounded-lg border border-gray-700/50">
            <p class="text-sm text-gray-400 flex justify-between"><span>Aluno:</span> <span class="text-white font-semibold">{{ mensalidadeSelecionada?.inscricao?.usuario?.nome }}</span></p>
            <p class="text-sm text-gray-400 flex justify-between mt-2"><span>Vencimento:</span> <span class="text-white">{{ formatarData(mensalidadeSelecionada?.data_vencimento) }}</span></p>
            <p class="text-sm text-gray-400 flex justify-between mt-2 border-t border-gray-700 pt-2"><span>Valor Original:</span> <span class="text-teal-400 font-mono">R$ {{ formatarPreco(mensalidadeSelecionada?.valor) }}</span></p>
        </div>
        <form @submit.prevent="confirmarPagamentoManual" class="space-y-4">
          <div>
            <label class="block text-xs text-gray-400 mb-1 ml-1">Data do Recebimento</label>
            <input v-model="formPagamento.data_pagamento" type="date" class="form-input-style" required />
          </div>
          <div>
            <label class="block text-xs text-gray-400 mb-1 ml-1">Valor Recebido (R$)</label>
            <input v-model="formPagamento.valor_pago" type="number" step="0.01" class="form-input-style" required />
          </div>
          <div>
            <label class="block text-xs text-gray-400 mb-1 ml-1">Método de Pagamento</label>
            <select v-model="formPagamento.metodo_pagamento" class="form-input-style" required>
                <option value="pix">PIX</option>
                <option value="dinheiro">Dinheiro</option>
                <option value="cartao_credito">Cartão de Crédito</option>
                <option value="cartao_debito">Cartão de Débito</option>
            </select>
          </div>
          <div class="flex justify-end gap-3 mt-8 pt-4 border-t border-gray-700">
            <button type="button" @click="fecharModal" class="px-4 py-2 text-gray-400 hover:text-white transition font-medium text-sm">Cancelar</button>
            <button type="submit" :disabled="salvandoPagamento" class="bg-teal-700 hover:bg-teal-600 text-white px-6 py-2 rounded-lg font-semibold disabled:opacity-50 transition text-sm shadow-lg shadow-teal-900/20">
                {{ salvandoPagamento ? 'Salvando...' : 'Confirmar Baixa' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

const mensalidades = ref([]);
const paginacao = ref({});
const loading = ref(false);
const modalAberto = ref(false);
const mensalidadeSelecionada = ref(null);
const salvandoPagamento = ref(false);

const filtros = reactive({
    search: '',
    status: '',
    mes: new Date().toISOString().slice(0, 7)
});

const formPagamento = reactive({
    data_pagamento: '',
    valor_pago: '',
    metodo_pagamento: 'pix'
});

// --- FUNÇÃO CORRETIVA: USA AXIOS PARA OBTER BLOB ---
// Isso evita que o Vue Router se intrometa, pois não há navegação de URL,
// apenas uma chamada de API e a criação de um Blob URL local.
const abrirAnexo = async (id) => {
    try {
        const response = await axios.get(`/api/mensalidades/${id}/comprovante`, {
            responseType: 'blob' // Importante para receber o arquivo
        });

        // Cria uma URL temporária para o blob
        const fileURL = window.URL.createObjectURL(new Blob([response.data], { type: response.headers['content-type'] }));

        // Abre em nova aba
        window.open(fileURL, '_blank');

    } catch (error) {
        console.error("Erro ao abrir anexo:", error);
        alert('Não foi possível carregar o arquivo. Verifique se ele ainda existe no servidor.');
    }
};

const fetchMensalidades = async (page = 1) => {
    loading.value = true;
    try {
        const params = { ...filtros, page };
        const response = await axios.get('/api/mensalidades', { params });
        mensalidades.value = response.data.data;
        paginacao.value = response.data;
    } catch (error) {
        console.error("Erro ao buscar mensalidades:", error);
    } finally {
        loading.value = false;
    }
};

const mudarPagina = (page) => {
    if (page >= 1 && page <= paginacao.value.last_page) {
        fetchMensalidades(page);
    }
};

const aprovar = async (id) => {
    if(!confirm('Confirmar o recebimento deste pagamento? O status mudará para PAGO.')) return;
    try {
        await axios.post(`/api/mensalidades/${id}/aprovar`);
        fetchMensalidades(paginacao.value.current_page);
    } catch (error) {
        alert('Erro ao aprovar: ' + (error.response?.data?.message || 'Erro desconhecido'));
    }
};

const rejeitar = async (id) => {
    if(!confirm('Rejeitar este comprovante? O status voltará para PENDENTE para que o aluno envie outro.')) return;
    try {
        await axios.post(`/api/mensalidades/${id}/rejeitar`);
        fetchMensalidades(paginacao.value.current_page);
    } catch (error) {
        alert('Erro ao rejeitar: ' + (error.response?.data?.message || 'Erro desconhecido'));
    }
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

const fecharModal = () => {
    modalAberto.value = false;
    mensalidadeSelecionada.value = null;
};

const confirmarPagamentoManual = async () => {
    if (!mensalidadeSelecionada.value) return;
    salvandoPagamento.value = true;
    try {
        await axios.post(`/api/mensalidades/${mensalidadeSelecionada.value.id}/pagar`, formPagamento);
        fetchMensalidades(paginacao.value.current_page);
        fecharModal();
    } catch (error) {
        alert('Erro ao registrar pagamento: ' + (error.response?.data?.message || 'Verifique os dados.'));
    } finally {
        salvandoPagamento.value = false;
    }
};

const formatarPreco = (val) => parseFloat(val).toFixed(2).replace('.', ',');
const formatarData = (data) => {
    if (!data) return '-';
    const [ano, mes, dia] = data.split('-');
    return `${dia}/${mes}/${ano}`;
};
const statusClass = (status) => {
    const classes = {
        'pendente': 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
        'em_analise': 'bg-blue-500/10 text-blue-400 border-blue-500/30',
        'paga': 'bg-green-500/10 text-green-400 border-green-500/30',
        'atrasada': 'bg-red-500/10 text-red-400 border-red-500/30',
        'cancelada': 'bg-gray-500/10 text-gray-400 border-gray-500/30',
    };
    return classes[status] || 'bg-gray-500/10 text-gray-400';
};

onMounted(() => {
    fetchMensalidades();
});
</script>

<style scoped>
.form-input-style {
  @apply w-full p-2.5 rounded-lg bg-[#242424] text-white border border-[#333] focus:border-teal-500 focus:ring-1 focus:ring-teal-500 focus:outline-none text-sm transition-all placeholder-gray-500;
}
</style>
