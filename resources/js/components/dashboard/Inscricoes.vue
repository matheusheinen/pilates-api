<template>
  <div class="bg-[#151515] p-6 rounded-xl text-white">

    <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-6 gap-4">
      <div>
        <h2 class="text-lg font-semibold">Gestão de Inscrições</h2>
        <p class="text-[#a0a0a0]">Visualize os contratos e status de matrícula dos alunos.</p>
      </div>

      <div v-if="usuarioId">
        <router-link
          v-if="!loading && podeCriarInscricao"
          :to="{ name: 'matricula', params: { id: usuarioId } }"
          class="w-full sm:w-auto px-4 py-2 rounded-lg bg-teal-700 hover:bg-teal-600 font-semibold transition-colors text-sm flex items-center justify-center gap-2 shadow-lg shadow-teal-900/20"
        >
          <span>+</span> Nova Inscrição
        </router-link>

        <button
          v-else
          disabled
          class="w-full sm:w-auto px-4 py-2 rounded-lg bg-[#2a2a2a] text-gray-500 font-semibold cursor-not-allowed text-sm flex items-center justify-center gap-2 border border-[#333]"
          title="Aluno já possui inscrição vigente."
        >
          <span>+</span> Nova Inscrição
        </button>
      </div>
    </div>

    <div class="bg-[#1e1e1e] rounded-xl border border-[#333] overflow-hidden shadow-sm relative">

      <div v-if="loading" class="absolute inset-0 bg-[#1e1e1e]/80 z-10 flex items-center justify-center">
        <div class="text-teal-500 font-semibold animate-pulse">Carregando inscrições...</div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left text-gray-300">
          <thead class="bg-[#252525] text-gray-400 uppercase text-xs">
            <tr>
              <th v-if="!usuarioId" class="px-6 py-3 font-medium tracking-wider">Aluno</th>
              <th class="px-6 py-3 font-medium tracking-wider">Plano</th>
              <th class="px-6 py-3 font-medium tracking-wider">Data Início</th>
              <th class="px-6 py-3 font-medium tracking-wider">Status</th>
              <th class="px-6 py-3 font-medium tracking-wider text-right">Ações</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#333]">
            <tr v-if="!loading && inscricoes.length === 0">
                <td :colspan="usuarioId ? 4 : 5" class="px-6 py-8 text-center text-gray-500 italic">
                    Nenhuma inscrição encontrada.
                </td>
            </tr>

            <tr v-for="inscricao in inscricoes" :key="inscricao.id" class="hover:bg-[#2a2a2a] transition duration-150">
              <td v-if="!usuarioId" class="px-6 py-4 font-medium text-white">
                {{ inscricao.usuario?.nome || 'Usuário Desconhecido' }}
              </td>
              <td class="px-6 py-4 text-sm text-gray-300">
                {{ inscricao.plano?.nome || 'Sem Plano' }}
              </td>
              <td class="px-6 py-4 text-sm">
                {{ formatarData(inscricao.data_inicio) }}
              </td>
              <td class="px-6 py-4">
                <span :class="statusClass(inscricao.status)" class="px-2 py-1 rounded text-[10px] font-bold uppercase border border-white/5">
                  {{ inscricao.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-right">
                <button
                    @click="editarInscricao(inscricao.id)"
                    :disabled="inscricao.status === 'cancelada'"
                    :class="[
                        inscricao.status === 'cancelada'
                        ? 'text-gray-600 border-gray-700 cursor-not-allowed'
                        : 'text-teal-500 hover:text-teal-400 border-teal-500/30 hover:bg-teal-500/10'
                    ]"
                    class="text-sm font-semibold border px-3 py-1 rounded transition"
                    title="Inscrições canceladas não podem ser editadas"
                >
                    {{ inscricao.status === 'cancelada' ? 'Arquivada' : 'Editar' }}
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
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useRouter, useRoute } from 'vue-router';

const router = useRouter();
const route = useRoute();
const inscricoes = ref([]);
const loading = ref(true);
const usuarioId = route.query.usuario_id;

const podeCriarInscricao = computed(() => {
    if (inscricoes.value.length === 0) return true;
    const temInscricaoVigente = inscricoes.value.some(inscricao => inscricao.status !== 'cancelada');
    return !temInscricaoVigente;
});

const fetchInscricoes = async () => {
    loading.value = true;
    try {
        let url = '/api/inscricoes';
        if (usuarioId) url += `?usuario_id=${usuarioId}`;
        const response = await axios.get(url);
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

const statusClass = (status) => {
    const classes = {
        'ativa': 'bg-green-500/10 text-green-400 border-green-500/30',
        'inativa': 'bg-red-500/10 text-red-400 border-red-500/30',
        'trancada': 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
        'cancelada': 'bg-gray-500/10 text-gray-400 border-gray-500/30',
    };
    return classes[status] || 'bg-gray-700 text-gray-300';
};

const editarInscricao = (id) => {
    router.push({ name: 'editar-inscricao', params: { id } });
};

onMounted(fetchInscricoes);
</script>
