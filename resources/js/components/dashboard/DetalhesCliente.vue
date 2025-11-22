<template>
  <div v-if="loading" class="text-center text-gray-400">Carregando dados do cliente...</div>
  <div v-else-if="cliente" class="space-y-6">
    <div class="flex justify-between items-center">
      <h2 class="text-2xl font-bold text-white">{{ cliente.nome }}</h2>
      <router-link to="/dashboard/clientes" class="text-sm font-semibold text-teal-500 hover:text-teal-400">&larr; Voltar para a lista</router-link>
    </div>

    <div class="bg-[#151515] p-6 rounded-xl">
        <h3 class="text-lg font-semibold text-white mb-4">Ações Rápidas</h3>
        <div class="flex flex-wrap gap-4">

            <router-link :to="{ name: 'avaliacao-postural', params: { id: cliente.id } }" class="action-button-sm">
              Cadastrar Nova Ficha
            </router-link>

            <router-link
                :to="{ name: 'matricula', params: { id: cliente.id } }"
                class="action-button-sm bg-blue-600 hover:bg-blue-500 text-white flex items-center justify-center transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Criar Nova Matrícula
            </router-link>

            <router-link
                :to="{ name: 'listagem-inscricoes', query: { usuario_id: cliente.id } }"
                class="action-button-sm bg-indigo-600 hover:bg-indigo-500 text-white flex items-center justify-center transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Ver Contratos (Histórico)
            </router-link>



            <button class="action-button-sm bg-yellow-600 hover:bg-yellow-500">Histórico de Pagamentos</button>
        </div>
    </div>

    <div class="bg-[#151515] p-6 rounded-xl">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-white">Informações Pessoais e Clínicas</h3>
        <button @click="irParaEdicao" class="action-button-sm bg-gray-600 hover:bg-gray-500">
          Editar Cadastro
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm text-gray-200">
        <div><strong class="text-gray-400">Data Nasc.:</strong> <p>{{ formatarData(cliente.data_nascimento) }}</p></div>
        <div><strong class="text-gray-400">Sexo:</strong> <p>{{ cliente.genero || 'Não informado' }}</p></div>
        <div><strong class="text-gray-400">CPF:</strong> <p>{{ cliente.cpf ? formatarCPF(cliente.cpf) : 'Não informado' }}</p></div>
        <div><strong class="text-gray-400">Celular:</strong> <p>{{ cliente.celular || 'Não informado' }}</p></div>
        <div><strong class="text-gray-400">Email:</strong> <p>{{ cliente.email }}</p></div>
        <div><strong class="text-gray-400">Profissão:</strong> <p>{{ cliente.profissao || 'Não informado' }}</p></div>
        <div><strong class="text-gray-400">Lateralidade:</strong> <p>{{ cliente.lateralidade || 'Não informado' }}</p></div>

        <div class="bg-teal-900/20 p-2 rounded border border-teal-900/50">
            <strong class="text-teal-400">Altura Atual:</strong>
            <p>{{ ultimaAvaliacao?.altura ? `${ultimaAvaliacao.altura} m` : '---' }}</p>
        </div>
        <div class="bg-teal-900/20 p-2 rounded border border-teal-900/50">
            <strong class="text-teal-400">Peso Atual:</strong>
            <p>{{ ultimaAvaliacao?.peso ? `${ultimaAvaliacao.peso} kg` : '---' }}</p>
            <span v-if="ultimaAvaliacao" class="text-xs text-gray-500">Ref: {{ formatarData(ultimaAvaliacao.data_avaliacao) }}</span>
        </div>
      </div>

      <div class="mt-4 border-t border-gray-700 pt-4 space-y-4">
        <div>
          <strong class="text-gray-400">Queixa Principal (Atual):</strong>
          <p class="mt-1 text-sm text-gray-200">{{ ultimaAvaliacao?.queixa_principal || 'Nenhuma avaliação cadastrada.' }}</p>
        </div>
        <div>
          <strong class="text-gray-400">Diagnóstico Clínico (Atual):</strong>
          <p class="mt-1 text-sm text-gray-200">{{ ultimaAvaliacao?.diagnostico_clinico || 'Nenhuma avaliação cadastrada.' }}</p>
        </div>
      </div>
    </div>

    <div class="bg-[#151515] p-6 rounded-xl">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-white">Histórico de Fichas de Avaliação</h3>
      </div>
      <table class="min-w-full divide-y divide-gray-700 mt-4">
        <thead class="bg-[#101010]">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Data</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Peso / Altura</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Observações</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase">Ações</th>
          </tr>
        </thead>
        <tbody class="bg-[#151515] divide-y divide-gray-700">
          <tr v-if="loadingAvaliacoes" class="text-center text-gray-400">
            <td colspan="4" class="px-6 py-4">Carregando histórico...</td>
          </tr>
          <tr v-else-if="avaliacoes.length === 0" class="text-center text-gray-500">
            <td colspan="4" class="px-6 py-4">Nenhuma ficha cadastrada.</td>
          </tr>
          <tr v-else v-for="ficha in avaliacoes" :key="ficha.id">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-200">{{ formatarData(ficha.data_avaliacao) }}</td>

            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                {{ ficha.peso ? `${ficha.peso}kg` : '-' }} / {{ ficha.altura ? `${ficha.altura}m` : '-' }}
            </td>

            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-200">{{ ficha.observacoes || 'N/A' }}</td>

            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <router-link :to="{ name: 'VisualizarAvaliacao', params: { id: ficha.id } }" class="text-teal-500 hover:text-teal-400">
                Visualizar
              </router-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

  </div>
  <div v-else class="text-center text-red-400">Erro ao carregar os dados do cliente.</div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const props = defineProps({
  id: {
    type: [String, Number],
    required: true
  }
});

const route = useRoute();
const router = useRouter();

const cliente = ref(null);
const loading = ref(true);

const avaliacoes = ref([]);
const loadingAvaliacoes = ref(true);

// COMPUTED: Pega a avaliação mais recente para exibir no topo
const ultimaAvaliacao = computed(() => {
    if (avaliacoes.value && avaliacoes.value.length > 0) {
        // Ordena descrescente por data (assumindo string ISO 'YYYY-MM-DD')
        // Se a API já devolver ordenado, o .sort é apenas garantia
        const sorted = [...avaliacoes.value].sort((a, b) => new Date(b.data_avaliacao) - new Date(a.data_avaliacao));
        return sorted[0];
    }
    return null;
});

const fetchCliente = async () => {
  try {
    const response = await axios.get(`/api/usuarios/${props.id}`);
    // Ajustado conforme retorno padrão do Laravel Resources
    cliente.value = response.data.data || response.data;
  } catch (error) {
    console.error("Erro ao buscar dados do cliente:", error);
  } finally {
    loading.value = false;
  }
};

const fetchAvaliacoes = async () => {
  try {
    const response = await axios.get(`/api/usuarios/${props.id}/avaliacoes`);
    // Garante array mesmo se vazio
    if (response.data && Array.isArray(response.data.data)) {
      avaliacoes.value = response.data.data;
    } else if (Array.isArray(response.data)) {
      avaliacoes.value = response.data;
    } else {
      avaliacoes.value = [];
    }
  } catch (error) {
    console.error("Erro ao buscar histórico de avaliações:", error);
    avaliacoes.value = [];
  } finally {
    loadingAvaliacoes.value = false;
  }
};

const irParaEdicao = () => {
  router.push({ name: 'EditarCliente', params: { id: props.id } });
};

const formatarData = (data) => {
  if (!data) return 'N/A';
  // Ajuste para timezone local se necessário
  return new Date(data).toLocaleDateString('pt-BR', { timeZone: 'UTC' });
};

// Helper simples para CPF (caso o back-end não mande formatado)
const formatarCPF = (cpf) => {
    return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, "$1.$2.$3-$4");
};

onMounted(() => {
  fetchCliente();
  fetchAvaliacoes();
});
</script>

<style scoped>
.action-button-sm {
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-weight: 600;
  color: white;
  background-color: #0d9488;
  transition: background-color 0.2s;
}
.action-button-sm:hover {
  background-color: #0f766e;
}
</style>
