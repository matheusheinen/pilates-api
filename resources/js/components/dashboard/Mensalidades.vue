<template>
  <div class="space-y-6">

    <div class="flex flex-col md:flex-row justify-between items-center gap-4">
      <h1 class="text-2xl font-bold text-white">Gestão de Mensalidades</h1>

      <div class="flex gap-2">
        <button @click="gerarMensalidades" :disabled="gerando" class="btn-secondary flex items-center gap-2">
          <span v-if="gerando" class="animate-spin">↻</span>
          <span>{{ gerando ? 'Gerando...' : 'Gerar Mensalidades do Mês' }}</span>
        </button>
      </div>
    </div>

    <div class="bg-[#1a1a1a] p-4 rounded-lg border border-gray-700 flex flex-wrap gap-4 items-end">
      <div class="flex-1 min-w-[200px]">
        <label class="text-xs text-gray-400 mb-1 block">Buscar Aluno</label>
        <input v-model="filtros.search" @input="fetchMensalidades" type="text" placeholder="Nome do aluno..." class="form-input" />
      </div>

      <div class="w-40">
        <label class="text-xs text-gray-400 mb-1 block">Mês de Vencimento</label>
        <input v-model="filtros.mes" @change="fetchMensalidades" type="month" class="form-input text-white" />
      </div>

      <div class="w-40">
        <label class="text-xs text-gray-400 mb-1 block">Status</label>
        <select v-model="filtros.status" @change="fetchMensalidades" class="form-input">
          <option value="">Todos</option>
          <option value="pendente">Pendente</option>
          <option value="em_analise">Em Análise</option>
          <option value="paga">Paga</option>
          <option value="atrasada">Atrasada</option>
        </select>
      </div>
    </div>

    <div class="bg-[#1a1a1a] rounded-lg border border-gray-700 overflow-hidden relative">

      <div v-if="loading" class="absolute inset-0 bg-[#1a1a1a]/80 z-10 flex items-center justify-center">
        <div class="text-teal-500 font-semibold animate-pulse">Carregando...</div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-gray-300">
          <thead class="bg-[#252525] text-gray-400 uppercase text-xs">
            <tr>
              <th class="p-4">Aluno</th>
              <th class="p-4">Plano</th>
              <th class="p-4">Vencimento</th>
              <th class="p-4">Valor</th>
              <th class="p-4">Status</th>
              <th class="p-4 text-right">Ações</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-700">
            <tr v-for="mensalidade in mensalidades" :key="mensalidade.id" class="hover:bg-white/5 transition">
              <td class="p-4 font-medium text-white">{{ mensalidade.inscricao?.usuario?.nome || 'N/A' }}</td>
              <td class="p-4 text-sm">{{ mensalidade.inscricao?.plano?.nome || 'N/A' }}</td>
              <td class="p-4">{{ formatarData(mensalidade.data_vencimento) }}</td>
              <td class="p-4 font-mono text-teal-400">R$ {{ formatarPreco(mensalidade.valor) }}</td>

              <td class="p-4">
                <span :class="statusClass(mensalidade.status)" class="px-2 py-1 rounded text-xs font-bold uppercase whitespace-nowrap">
                  {{ mensalidade.status.replace('_', ' ') }}
                </span>
              </td>

              <td class="p-4 text-right">

                <div v-if="mensalidade.status === 'em_analise'" class="flex justify-end items-center gap-2">
                    <button @click="verComprovante(mensalidade.pagamento?.comprovante_path)" class="text-blue-400 hover:text-blue-300 text-xs underline mr-2" title="Ver Comprovante">
                        Ver Anexo
                    </button>
                    <button @click="aprovar(mensalidade.id)" class="bg-green-600 hover:bg-green-500 text-white p-1.5 rounded transition" title="Aprovar">
                        ✓
                    </button>
                    <button @click="rejeitar(mensalidade.id)" class="bg-red-600 hover:bg-red-500 text-white p-1.5 rounded transition" title="Rejeitar">
                        ✕
                    </button>
                </div>

                <button v-else-if="['pendente', 'atrasada'].includes(mensalidade.status)"
                        @click="abrirModalPagamento(mensalidade)"
                        class="text-teal-500 hover:text-teal-400 text-sm font-semibold border border-teal-500/50 px-3 py-1 rounded hover:bg-teal-500/10 transition">
                  Baixa Manual
                </button>

                <div v-else-if="mensalidade.status === 'paga'" class="text-xs text-gray-500 flex flex-col items-end">
                    <span class="text-green-500/70 font-semibold">Pago em {{ formatarData(mensalidade.pagamento?.data_pagamento) }}</span>
                    <button v-if="mensalidade.pagamento?.comprovante_path" @click="verComprovante(mensalidade.pagamento?.comprovante_path)" class="text-blue-500 hover:text-blue-400 underline mt-1">
                        Ver Comprovante
                    </button>
                </div>

              </td>
            </tr>
            <tr v-if="!loading && mensalidades.length === 0">
                <td colspan="6" class="p-8 text-center text-gray-500">Nenhuma mensalidade encontrada com os filtros atuais.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="p-4 border-t border-gray-700 flex justify-between items-center" v-if="paginacao.total > 0">
          <span class="text-xs text-gray-500">Mostrando {{ paginacao.from }} - {{ paginacao.to }} de {{ paginacao.total }}</span>
          <div class="flex gap-2">
              <button @click="mudarPagina(paginacao.current_page - 1)" :disabled="paginacao.current_page === 1" class="px-3 py-1 bg-gray-700 rounded text-sm disabled:opacity-50 transition hover:bg-gray-600">&lt;</button>
              <button @click="mudarPagina(paginacao.current_page + 1)" :disabled="paginacao.current_page === paginacao.last_page" class="px-3 py-1 bg-gray-700 rounded text-sm disabled:opacity-50 transition hover:bg-gray-600">&gt;</button>
          </div>
      </div>
    </div>

    <div v-if="modalAberto" class="fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4">
      <div class="bg-[#1f1f1f] rounded-xl border border-gray-600 w-full max-w-md p-6 shadow-2xl">
        <h3 class="text-xl font-bold text-white mb-4">Baixa Manual de Pagamento</h3>

        <div class="mb-4 p-3 bg-gray-800 rounded border border-gray-700">
            <p class="text-sm text-gray-400">Aluno: <span class="text-white font-semibold">{{ mensalidadeSelecionada?.inscricao?.usuario?.nome }}</span></p>
            <p class="text-sm text-gray-400 mt-1">Vencimento: <span class="text-white">{{ formatarData(mensalidadeSelecionada?.data_vencimento) }}</span></p>
            <p class="text-sm text-gray-400 mt-1">Valor Original: <span class="text-teal-400">R$ {{ formatarPreco(mensalidadeSelecionada?.valor) }}</span></p>
        </div>

        <form @submit.prevent="confirmarPagamentoManual" class="space-y-4">
          <div>
            <label class="block text-xs text-gray-400 mb-1">Data do Recebimento</label>
            <input v-model="formPagamento.data_pagamento" type="date" class="form-input" required />
          </div>

          <div>
            <label class="block text-xs text-gray-400 mb-1">Valor Recebido (R$)</label>
            <input v-model="formPagamento.valor_pago" type="number" step="0.01" class="form-input" required />
          </div>

          <div>
            <label class="block text-xs text-gray-400 mb-1">Método de Pagamento</label>
            <select v-model="formPagamento.metodo_pagamento" class="form-input" required>
                <option value="pix">PIX</option>
                <option value="dinheiro">Dinheiro</option>
                <option value="cartao_credito">Cartão de Crédito</option>
                <option value="cartao_debito">Cartão de Débito</option>
            </select>
          </div>

          <div class="flex justify-end gap-3 mt-6">
            <button type="button" @click="fecharModal" class="px-4 py-2 text-gray-300 hover:text-white transition">Cancelar</button>
            <button type="submit" :disabled="salvandoPagamento" class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-2 rounded font-semibold disabled:opacity-50 transition">
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
const loading = ref(false); // AJUSTE 2: Variável de loading
const gerando = ref(false);
const modalAberto = ref(false);
const mensalidadeSelecionada = ref(null);
const salvandoPagamento = ref(false);

const filtros = reactive({
    search: '',
    status: '',
    mes: new Date().toISOString().slice(0, 7) // Mês atual padrão
});

const formPagamento = reactive({
    data_pagamento: '',
    valor_pago: '',
    metodo_pagamento: 'pix'
});

// --- FUNÇÕES DE BUSCA E LISTAGEM ---

const fetchMensalidades = async (page = 1) => {
    loading.value = true; // Inicia loading
    try {
        const params = { ...filtros, page };
        const response = await axios.get('/api/mensalidades', { params });
        mensalidades.value = response.data.data;
        paginacao.value = response.data;
    } catch (error) {
        console.error("Erro ao buscar mensalidades:", error);
    } finally {
        loading.value = false; // Finaliza loading
    }
};

const mudarPagina = (page) => {
    if (page >= 1 && page <= paginacao.value.last_page) {
        fetchMensalidades(page);
    }
};

// --- FUNÇÕES DE AÇÃO DO ADMIN ---

const gerarMensalidades = async () => {
    if (!confirm('Deseja gerar as mensalidades para todas as inscrições ativas que ainda não possuem cobrança para este mês?')) return;

    gerando.value = true;
    try {
        const response = await axios.post('/api/mensalidades/gerar-massivo');
        alert(response.data.message);
        fetchMensalidades();
    } catch (error) {
        alert('Erro ao gerar mensalidades: ' + (error.response?.data?.message || error.message));
    } finally {
        gerando.value = false;
    }
};

const aprovar = async (id) => {
    if(!confirm('Confirmar o recebimento deste pagamento? O status mudará para PAGO.')) return;
    try {
        await axios.post(`/api/mensalidades/${id}/aprovar`);

        // AJUSTE 3: Limpar filtro de mês para mostrar a nova mensalidade gerada
        filtros.mes = '';

        fetchMensalidades(paginacao.value.current_page);
        alert('Pagamento aprovado e próxima mensalidade gerada!');
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

const verComprovante = (path) => {
    if (!path) return;
    const url = `/storage/${path}`;
    window.open(url, '_blank');
};

// --- MODAL DE BAIXA MANUAL ---

const abrirModalPagamento = (mensalidade) => {
    mensalidadeSelecionada.value = mensalidade;

    // AJUSTE 1: Data correta no Fuso Horário local
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

        // AJUSTE 3: Limpar filtro de mês para ver a renovação
        filtros.mes = '';

        fetchMensalidades(paginacao.value.current_page);
        fecharModal();
        alert('Baixa registrada e próxima mensalidade gerada!');
    } catch (error) {
        alert('Erro ao registrar pagamento: ' + (error.response?.data?.message || 'Verifique os dados.'));
        console.error(error);
    } finally {
        salvandoPagamento.value = false;
    }
};

// --- HELPERS ---

const formatarPreco = (val) => parseFloat(val).toFixed(2).replace('.', ',');
const formatarData = (data) => {
    if (!data) return '-';
    const [ano, mes, dia] = data.split('-');
    return `${dia}/${mes}/${ano}`;
};
const statusClass = (status) => {
    const classes = {
        'pendente': 'bg-yellow-500/20 text-yellow-400',
        'em_analise': 'bg-blue-500/20 text-blue-400',
        'paga': 'bg-green-500/20 text-green-400',
        'atrasada': 'bg-red-500/20 text-red-400',
        'cancelada': 'bg-gray-500/20 text-gray-400',
    };
    return classes[status] || 'bg-gray-500/20 text-gray-400';
};

onMounted(() => {
    fetchMensalidades();
});
</script>

<style scoped>
.form-input {
  @apply w-full p-2 rounded bg-[#0f1616] text-white border border-gray-700 focus:border-teal-500 focus:outline-none text-sm transition-all;
}
.btn-secondary {
  @apply bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded text-sm transition border border-gray-600;
}
</style>
