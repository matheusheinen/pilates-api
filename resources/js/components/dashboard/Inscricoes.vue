<template>
  <div class="bg-[#151515] p-6 rounded-xl text-white">
    <h2 class="text-2xl font-bold text-teal-500 mb-6">Gestão de Inscrições</h2>

    <div v-if="loading" class="text-center py-10 text-gray-400">Carregando inscrições...</div>
    <div v-else-if="!inscricoes.length" class="text-center py-10 text-gray-500 border border-dashed border-gray-700 rounded-lg">
        Nenhuma inscrição encontrada para este cliente.
    </div>

    <div v-else>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-700">
          <thead class="bg-[#242424]">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Aluno</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Plano</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Início</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase">Ações</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-800">
            <tr v-for="inscricao in inscricoes" :key="inscricao.id" class="hover:bg-[#1a1a1a]">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                  {{ inscricao.usuario?.nome || 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-teal-400">
                  {{ inscricao.plano?.nome || 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                  {{ formatarData(inscricao.data_inicio) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                      :class="{'bg-green-600/30 text-green-300': inscricao.status === 'ativa', 'bg-red-600/30 text-red-300': inscricao.status === 'cancelada', 'bg-yellow-600/30 text-yellow-300': inscricao.status === 'trancada'}">
                      {{ inscricao.status.toUpperCase() }}
                  </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <button @click="editarInscricao(inscricao.id)" class="text-blue-400 hover:text-blue-300">
                    Editar
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter, useRoute } from 'vue-router';

const router = useRouter();
const route = useRoute();

const inscricoes = ref([]);
const loading = ref(true);

const fetchInscricoes = async () => {
    try {
        // Pega o ID do usuário da URL, se existir (vindo do DetalhesCliente)
        const usuarioId = route.query.usuario_id;
        let url = '/api/inscricoes';

        if (usuarioId) {
            url += `?usuario_id=${usuarioId}`; // Aplica o filtro
        }

        const response = await axios.get(url);
        // O backend deve retornar com os relacionamentos usuario e plano
        inscricoes.value = response.data.data || response.data;
    } catch (error) {
        console.error("Erro ao buscar inscrições:", error);
    } finally {
        loading.value = false;
    }
};

const formatarData = (data) => {
    if (!data) return 'N/A';
    return new Date(data).toLocaleDateString('pt-BR', { timeZone: 'UTC' });
};

const editarInscricao = (id) => {
    // Redireciona para a rota de edição
    router.push({ name: 'editar-inscricao', params: { id: id } });
};

onMounted(fetchInscricoes);
</script>
<style scoped>
th, td { border-color: #333; }
</style>
