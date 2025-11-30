<template>
  <div v-if="loading" class="text-center text-gray-400 py-10 animate-pulse">Carregando dados da avaliação...</div>
  <div v-else-if="avaliacao" class="space-y-6">

    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-4">
      <div>
        <h2 class="text-2xl font-bold text-white">Detalhes da Avaliação Postural</h2>
        <p class="text-teal-400 text-sm mt-1">
            Avaliação realizada em: <span class="font-bold text-white">{{ formatarData(avaliacao.data_avaliacao) }}</span>
        </p>
        <p v-if="avaliacao.usuario" class="text-gray-500 text-xs mt-0.5">Aluno: {{ avaliacao.usuario.nome }}</p>
      </div>
      <button @click="voltar" class="text-sm font-semibold text-teal-500 hover:text-teal-400 border border-teal-500/30 px-4 py-2 rounded-lg hover:bg-teal-500/10 transition">
        &larr; Voltar
      </button>
    </div>

    <div class="bg-[#151515] p-6 rounded-xl border border-gray-800">
        <h3 class="text-lg font-semibold text-white mb-4 border-b border-gray-700 pb-2">Dados Clínicos</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-[#1e1e1e] p-3 rounded-lg">
                <strong class="text-gray-400 text-xs block uppercase">Peso</strong>
                <span class="text-white text-lg">{{ avaliacao.peso ? avaliacao.peso + ' kg' : '-' }}</span>
            </div>
            <div class="bg-[#1e1e1e] p-3 rounded-lg">
                <strong class="text-gray-400 text-xs block uppercase">Altura</strong>
                <span class="text-white text-lg">{{ avaliacao.altura ? avaliacao.altura + ' m' : '-' }}</span>
            </div>
            <div class="sm:col-span-2 bg-[#1e1e1e] p-3 rounded-lg">
                <strong class="text-gray-400 text-xs block uppercase">Diagnóstico Clínico</strong>
                <span class="text-white">{{ avaliacao.diagnostico_clinico || 'Não informado' }}</span>
            </div>
            <div class="lg:col-span-4 bg-[#1e1e1e] p-3 rounded-lg">
                <strong class="text-gray-400 text-xs block uppercase">Queixa Principal</strong>
                <span class="text-white">{{ avaliacao.queixa_principal || 'Não informado' }}</span>
            </div>
        </div>

        <div v-if="avaliacao.caminho_anexo" class="mt-4 pt-4 border-t border-gray-800">
            <strong class="text-gray-400 text-xs block uppercase mb-2">Anexo / Exame</strong>
            <a :href="`/storage/${avaliacao.caminho_anexo}`" target="_blank" class="inline-flex items-center gap-2 text-teal-400 hover:text-teal-300 bg-teal-900/20 px-3 py-2 rounded-lg border border-teal-900/50 transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                Visualizar Arquivo Anexado
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="bg-[#151515] p-6 rounded-xl border border-gray-800">
            <h3 class="text-lg font-semibold text-teal-500 mb-4 border-b border-gray-700 pb-2">Vista Anterior</h3>
            <ul class="space-y-3 text-sm">
                <li v-for="(label, key) in camposAnterior" :key="key" class="flex justify-between items-start border-b border-gray-800/50 pb-2 last:border-0">
                    <span class="text-gray-400">{{ label }}:</span>
                    <span :class="getValorClass(avaliacao[key])" class="font-medium text-right max-w-[50%]">{{ avaliacao[key] || '-' }}</span>
                </li>
            </ul>
        </div>

        <div class="bg-[#151515] p-6 rounded-xl border border-gray-800">
            <h3 class="text-lg font-semibold text-teal-500 mb-4 border-b border-gray-700 pb-2">Vista Posterior</h3>
            <ul class="space-y-3 text-sm">
                <li v-for="(label, key) in camposPosterior" :key="key" class="flex justify-between items-start border-b border-gray-800/50 pb-2 last:border-0">
                    <span class="text-gray-400">{{ label }}:</span>
                    <span :class="getValorClass(avaliacao[key])" class="font-medium text-right max-w-[50%]">{{ avaliacao[key] || '-' }}</span>
                </li>
            </ul>
        </div>

        <div class="bg-[#151515] p-6 rounded-xl border border-gray-800">
            <h3 class="text-lg font-semibold text-teal-500 mb-4 border-b border-gray-700 pb-2">Vista Lateral</h3>
            <ul class="space-y-3 text-sm">
                <li v-for="(label, key) in camposLateral" :key="key" class="flex justify-between items-start border-b border-gray-800/50 pb-2 last:border-0">
                    <span class="text-gray-400">{{ label }}:</span>
                    <span :class="getValorClass(avaliacao[key])" class="font-medium text-right max-w-[50%]">{{ avaliacao[key] || '-' }}</span>
                </li>
            </ul>
        </div>
    </div>

    <div class="bg-[#151515] p-6 rounded-xl border border-gray-800">
      <h3 class="text-lg font-semibold text-white mb-4 border-b border-gray-700 pb-2">Observações Adicionais</h3>
      <p class="text-gray-300 text-sm leading-relaxed whitespace-pre-line">{{ avaliacao.observacoes || 'Nenhuma observação registrada.' }}</p>
    </div>

  </div>

  <div v-else class="text-center text-red-400 py-10 border border-red-900/30 bg-red-900/10 rounded-xl">
      Erro ao carregar os dados da avaliação. Tente novamente.
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const props = defineProps({
  id: { type: [String, Number], required: true }
});

const router = useRouter();
const loading = ref(true);
const avaliacao = ref(null);

// Dicionários de Campos (Mapeamento Chave -> Label)
const camposAnterior = {
    'anterior_cabeca': 'Cabeça',
    'anterior_ombros_altura': 'Altura dos Ombros',
    'anterior_maos_altura': 'Altura das Mãos',
    'anterior_tronco_rotacao': 'Rotação do Tronco',
    'anterior_angulo_tales': 'Ângulo de Tales',
    'anterior_cicatriz_umbilical': 'Cicatriz Umbilical',
    'anterior_iliacas_altura': 'Altura C. Ilíacas',
    'anterior_joelhos': 'Joelhos',
    'anterior_tornozelos': 'Tornozelos',
    'anterior_pes': 'Pés'
};

const camposPosterior = {
    'posterior_escapulas_altura': 'Altura das Escápulas',
    'posterior_escapula_alada': 'Escápula Alada',
    'posterior_gibosidade_toracica': 'Gibosidade Torácica',
    'posterior_pregas_gluteas': 'Pregas Glúteas',
    'posterior_pregas_popliteas': 'Pregas Poplíteas',
    'posterior_lombar_concavidade': 'Concav. Lombar',
    'posterior_toracica_concavidade': 'Concav. Torácica',
    'posterior_cervical_concavidade': 'Concav. Cervical'
};

const camposLateral = {
    'lateral_cabeca': 'Cabeça',
    'lateral_cervical': 'Cervical',
    'lateral_ombro': 'Ombro',
    'lateral_membro_superior': 'Membro Superior',
    'lateral_toracica': 'Torácica',
    'lateral_tronco_rotacao': 'Rotação Tronco',
    'lateral_abdomen': 'Abdômen',
    'lateral_lombar': 'Lombar',
    'lateral_pelve': 'Pelve',
    'lateral_quadril': 'Quadril',
    'lateral_joelho': 'Joelho'
};

const fetchAvaliacao = async () => {
  try {
    const response = await axios.get(`/api/avaliacoes-posturais/${props.id}?with=usuario`);
    avaliacao.value = response.data.data || response.data;
  } catch (error) {
    console.error("Erro ao buscar dados:", error);
  } finally {
    loading.value = false;
  }
};

const voltar = () => {
  router.go(-1);
};

const formatarData = (data) => {
  if (!data) return 'N/A';
  return new Date(data).toLocaleDateString('pt-BR', { timeZone: 'UTC' });
};

// Helper de Estilo: Destaca alterações posturais (tudo que não for 'Normal', 'Simétrico', 'Alinhado')
const getValorClass = (valor) => {
    if (!valor) return 'text-gray-600';
    const normalValues = ['Normal', 'Simétrico', 'Simétricos', 'Simétricas', 'Alinhada', 'Ausente'];
    // Se o valor não estiver na lista de "Normais", pinta de amarelo/laranja para destaque
    if (!normalValues.includes(valor)) {
        return 'text-yellow-400 font-bold';
    }
    return 'text-green-400';
};

onMounted(fetchAvaliacao);
</script>
