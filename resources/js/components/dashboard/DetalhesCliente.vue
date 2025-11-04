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
            <router-link :to="{ name: 'avaliacao-postural', params: { id: cliente.id } }" class="action-button">
                Ver Ficha de Avaliação
            </router-link>
            <button class="action-button bg-blue-600 hover:bg-blue-500">Ver Inscrição</button>
            <button class="action-button bg-yellow-600 hover:bg-yellow-500">Histórico de Pagamentos</button>
        </div>
    </div>

    <div class="bg-[#151515] p-6 rounded-xl">
      <h3 class="text-lg font-semibold text-white mb-4">Informações Pessoais</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
        <div><strong class="text-gray-400">Email:</strong> <p>{{ cliente.email }}</p></div>
        <div><strong class="text-gray-400">Celular:</strong> <p>{{ cliente.celular || 'Não informado' }}</p></div>
        <div><strong class="text-gray-400">CPF:</strong> <p>{{ cliente.cpf || 'Não informado' }}</p></div>
        <div><strong class="text-gray-400">Data de Nascimento:</strong> <p>{{ cliente.data_nascimento || 'Não informado' }}</p></div>
        <div><strong class="text-gray-400">Gênero:</strong> <p class="capitalize">{{ cliente.genero || 'Não informado' }}</p></div>
        <div><strong class="text-gray-400">Profissão:</strong> <p>{{ cliente.profissao || 'Não informado' }}</p></div>
        <div><strong class="text-gray-400">Altura:</strong> <p>{{ cliente.altura ? `${cliente.altura} m` : 'Não informado' }}</p></div>
        <div><strong class="text-gray-400">Peso:</strong> <p>{{ cliente.peso ? `${cliente.peso} kg` : 'Não informado' }}</p></div>
      </div>
      <div class="mt-4" v-if="cliente.queixa_principal">
        <strong class="text-gray-400">Queixa Principal:</strong>
        <p class="mt-1 text-sm">{{ cliente.queixa_principal }}</p>
      </div>
    </div>
  </div>
  <div v-else class="text-center text-red-400">Erro ao carregar os dados do cliente.</div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

// `defineProps` é usado para receber o 'id' passado pela rota
const props = defineProps({
  id: {
    type: [String, Number],
    required: true
  }
});

const route = useRoute();
const cliente = ref(null);
const loading = ref(true);

// Busca os dados do cliente específico da API
const fetchCliente = async () => {
  try {
    // A rota GET /api/usuarios/{id} já existe graças ao Route::apiResource
    const response = await axios.get(`/api/usuarios/${props.id}`);
    cliente.value = response.data;
  } catch (error) {
    console.error("Erro ao buscar dados do cliente:", error);
  } finally {
    loading.value = false;
  }
};

onMounted(fetchCliente);
</script>

<style scoped>
.action-button {
  @apply px-4 py-2 rounded-lg bg-teal-700 hover:bg-teal-600 font-semibold transition-colors text-sm;
}
</style>
