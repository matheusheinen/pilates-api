<template>
  <div v-if="loading" class="text-center text-gray-400">Carregando dados da avaliação...</div>
  <div v-else-if="avaliacao" class="space-y-6">
    <div class="flex justify-between items-center">
      <h2 class="text-2xl font-bold text-white">Detalhes da Avaliação Postural</h2>
      <button @click="voltar" class="text-sm font-semibold text-teal-500 hover:text-teal-400">&larr; Voltar</button>
    </div>

    <div class="bg-[#151515] p-6 rounded-xl space-y-4">
      <div class="border-t border-gray-700 pt-4">
        <strong class="text-gray-400">Observações Adicionais:</strong>
        <p class="mt-1 text-sm text-gray-200">{{ avaliacao.observacoes || 'Não informado' }}</p>
      </div>

    </div>
  </div>
  <div v-else class="text-center text-red-400">Erro ao carregar os dados da avaliação.</div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const props = defineProps({
  id: {
    type: [String, Number],
    required: true
  }
});

const router = useRouter();
const loading = ref(true);
const avaliacao = ref(null);

// 1. Busca os dados da avaliação específica
const fetchAvaliacao = async () => {
  try {
    const response = await axios.get(`/api/avaliacoes-posturais/${props.id}?with=usuario`);
    avaliacao.value = response.data.data;
  } catch (error) {
    console.error("Erro ao buscar dados da avaliação:", error);
  } finally {
    loading.value = false;
  }
};

// 2. Função para o botão "Voltar"
const voltar = () => {
  router.go(-1); // Simplesmente volta para a página anterior
};

// Helper para formatar data
const formatarData = (data) => {
  if (!data) return 'N/A';
  return new Date(data).toLocaleDateString('pt-BR', { timeZone: 'UTC' });
};

onMounted(fetchAvaliacao);
</script>

<style scoped>
/* Estilos para a tela de visualização */
.text-gray-400 { color: #9ca3af; }
.text-white { color: #ffffff; }
.text-teal-500 { color: #14b8a6; }
.hover\:text-teal-400:hover { color: #2dd4bf; }
.bg-\[\#151515\] { background-color: #151515; }
.p-6 { padding: 1.5rem; }
.rounded-xl { border-radius: 0.75rem; }
.space-y-6 > :not([hidden]) ~ :not([hidden]) { margin-top: 1.5rem; }
.space-y-4 > :not([hidden]) ~ :not([hidden]) { margin-top: 1rem; }
.border-t { border-top-width: 1px; }
.border-gray-700 { border-color: #374151; }
.pt-4 { padding-top: 1rem; }
.mt-1 { margin-top: 0.25rem; }
.text-sm { font-size: 0.875rem; }
.text-gray-200 { color: #e5e7eb; }
</style>
