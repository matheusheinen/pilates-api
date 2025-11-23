<template>
  <div class="bg-[#151515] p-6 rounded-xl text-white">
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-teal-500">Gestão de Inscrições</h2>

      <router-link
        v-if="usuarioId"
        :to="{ name: 'matricula', params: { id: usuarioId } }"
        class="bg-green-600 hover:bg-green-500 text-white font-semibold py-2 px-4 rounded-lg transition-colors text-sm"
      >
        + Criar Nova Inscrição
      </router-link>
      </div>

    <div v-if="loading" class="text-center py-10 text-gray-400">Carregando inscrições...</div>
    <div v-else-if="!inscricoes.length" class="text-center py-10 text-gray-500 border border-dashed border-gray-700 rounded-lg">
        Nenhuma inscrição encontrada para este cliente.
    </div>

    <div v-else>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-700">
          <thead class="bg-[#242424]">
            <tr>
              <th v-if="!usuarioId" class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Aluno</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Plano</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Início</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase">Ações</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-800">
            <tr v-for="inscricao in inscricoes" :key="inscricao.id" class="hover:bg-[#1f1f1f] transition-colors">
              <td v-if="!usuarioId" class="px-6 py-4 whitespace-nowrap text-sm font-medium text-white">
                {{ inscricao.usuario ? inscricao.usuario.nome : 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ inscricao.plano ? inscricao.plano.nome : 'N/A' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">{{ formatarData(inscricao.data_inicio) }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="{'bg-green-600/20 text-green-400': inscricao.status === 'ativa', 'bg-red-600/20 text-red-400': inscricao.status === 'inativo' || inscricao.status === 'finalizada'}" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full capitalize">
                  {{ inscricao.status }}
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

// Variavel que será usada no v-if do botão
const usuarioId = route.query.usuario_id;

const fetchInscricoes = async () => {
    try {
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
/* Estilos existentes */
.table th, .table td {
  border-color: #333;
}
</style>
