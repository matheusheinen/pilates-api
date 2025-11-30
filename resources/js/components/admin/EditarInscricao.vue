<template>
  <div v-if="loading" class="text-center text-gray-400 mt-10">Carregando dados do contrato...</div>
  <div v-else-if="inscricao" class="space-y-6">

    <div class="flex justify-between items-center">
      <h2 class="text-2xl font-bold text-white">Editar Inscrição: <span class="text-teal-500">{{ inscricao.usuario?.nome || 'N/A' }}</span></h2>
      <router-link :to="{ name: 'admin-inscricoes', query: inscricao.usuario_id ? { usuario_id: inscricao.usuario_id } : {} }" class="text-sm font-semibold text-teal-500 hover:text-teal-400 flex items-center gap-1">
        <span>&larr;</span> Voltar para a Listagem
      </router-link>
    </div>

    <div v-if="isCancelada" class="mb-4 bg-red-500/10 border border-red-500/50 text-red-400 text-sm p-4 rounded-lg flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
        <strong>Atenção:</strong> Esta inscrição está CANCELADA e não pode ser editada. Para reativar o aluno, crie uma nova inscrição.
    </div>

    <div v-if="errorMessage" class="mb-4 bg-red-500/20 border border-red-700 text-red-300 text-sm p-3 rounded-lg">
      {{ errorMessage }}
    </div>

    <div v-if="successMessage" class="mb-4 bg-green-500/20 border border-green-700 text-green-300 text-sm p-3 rounded-lg">
      {{ successMessage }}
    </div>

    <form @submit.prevent="submitForm" class="bg-[#151515] p-8 rounded-xl border border-white/10 space-y-6 relative">

      <div v-if="isCancelada" class="absolute inset-0 bg-[#151515]/50 z-10 cursor-not-allowed rounded-xl"></div>

      <fieldset class="form-section border-gray-700">
        <legend class="form-legend">Dados do Contrato</legend>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

          <div>
            <label class="block text-xs text-gray-400 mb-1 ml-1">Status do Contrato</label>
            <select v-model="form.status" class="form-input" :disabled="isCancelada" :class="{'text-red-400': form.status !== 'ativa', 'text-green-400': form.status === 'ativa'}" required>
                <option value="ativa">ATIVA</option>
                <option value="inativa">INATIVA (Suspensa)</option>
                <option value="trancada">TRANCADA (Pausa)</option>
                <option value="cancelada">CANCELADA (Encerrada)</option>
            </select>
          </div>

          <div>
            <label class="block text-xs text-gray-400 mb-1 ml-1">Plano Selecionado</label>
            <select v-model="form.plano_id" class="form-input" required :disabled="saving || isCancelada">
                <option v-for="plano in planos" :key="plano.id" :value="plano.id">
                    {{ plano.nome }} ({{ limiteHorariosPorPlano(plano.id) }}x/semana)
                </option>
            </select>
          </div>

          <div>
            <label class="block text-xs text-gray-400 mb-1 ml-1">Data de Início</label>
            <input :value="inscricao.data_inicio" type="date" class="form-input opacity-50 cursor-not-allowed" disabled />
          </div>

        </div>
      </fieldset>

      <fieldset class="form-section border-gray-700">
        <legend class="form-legend">Horários Fixos Reservados</legend>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
          <div
            v-for="horario in horariosAgenda"
            :key="horario.id"
            @click="!isCancelada && toggleHorario(horario.id)"
            :class="['p-4 rounded-lg transition-all duration-200',
                     // Lógica de bloqueio visual se cancelada
                     isCancelada ? 'opacity-50 cursor-not-allowed' : '',
                     horario.ocupacao >= horario.vagas_totais || (form.horarios_agenda_ids.length >= limiteHorarios && !form.horarios_agenda_ids.includes(horario.id)) ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:border-teal-600',
                     horario.ocupacao >= horario.vagas_totais ? 'bg-red-900/40 border border-red-700' : 'border',
                     form.horarios_agenda_ids.includes(horario.id) ? 'bg-teal-700 border-teal-500 shadow-lg shadow-teal-900/30' : 'bg-[#1a1a1a] border-gray-700']"
          >
            <div class="flex justify-between items-start">
                <p class="font-semibold text-lg">{{ formatarDiaSemana(horario.dia_semana) }}</p>
                <input type="checkbox" :checked="form.horarios_agenda_ids.includes(horario.id)" :disabled="isCancelada || horario.ocupacao >= horario.vagas_totais"
                    class="h-5 w-5 rounded transition-colors duration-200" :class="[horario.ocupacao >= horario.vagas_totais ? 'bg-gray-600 cursor-not-allowed' : 'text-teal-600 bg-gray-700 border-gray-600 focus:ring-teal-500']"/>
            </div>
            <p class="text-xl font-bold mt-1">{{ horario.horario_inicio?.substring(0, 5) || 'N/A' }}</p>
            <div class="mt-2 text-xs">
                <p :class="[horario.ocupacao >= horario.vagas_totais ? 'text-red-300' : 'text-gray-300']">
                    Vagas: {{ horario.ocupacao }} / {{ horario.vagas_totais }}
                </p>
            </div>
          </div>
        </div>
      </fieldset>

      <div class="pt-4 border-t border-gray-700 flex justify-end">
        <button v-if="!isCancelada" type="submit" :disabled="saving || form.horarios_agenda_ids.length !== limiteHorarios"
            :class="[form.horarios_agenda_ids.length !== limiteHorarios ? 'bg-gray-600' : 'bg-teal-700 hover:bg-teal-600']"
            class="text-white font-semibold py-3 px-8 rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-teal-900/20">
          {{ saving ? 'Salvando...' : 'Salvar Alterações' }}
        </button>

        <router-link v-else :to="{ name: 'listagem-inscricoes' }" class="text-gray-400 hover:text-white font-semibold py-3 px-8">
            Voltar
        </router-link>
      </div>

    </form>
  </div>
  <div v-else class="text-center text-red-400 mt-10">Inscrição não encontrada.</div>
</template>

<script setup>
import { ref, onMounted, reactive, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const props = defineProps({ id: { type: [String, Number], required: true } });

const router = useRouter();
const loading = ref(true);
const saving = ref(false);
const inscricao = ref(null);
const planos = ref([]);
const horariosAgenda = ref([]);
const errorMessage = ref('');
const successMessage = ref('');

const form = reactive({
    usuario_id: null,
    plano_id: null,
    status: 'ativa',
    horarios_agenda_ids: [],
});

// COMPUTED: Verifica se está cancelada
const isCancelada = computed(() => {
    return inscricao.value?.status === 'cancelada';
});

// --- LÓGICA DE LIMITE ---
const limiteHorarios = computed(() => {
    const planoSelecionado = planos.value.find(p => p.id === form.plano_id);
    const limite = planoSelecionado ? parseInt(planoSelecionado.numero_aulas) : 0;
    return limite > 0 ? limite : 0;
});
const limiteHorariosPorPlano = (planoId) => {
    const plano = planos.value.find(p => p.id === planoId);
    return plano ? parseInt(plano.numero_aulas) : 0;
};

const adaptarEstruturaParaTemplate = (dadosInscricao) => {
    if (dadosInscricao.horarios_aluno && Array.isArray(dadosInscricao.horarios_aluno)) {
        dadosInscricao.horarios_aluno = dadosInscricao.horarios_aluno.map(agenda => {
            if (agenda.agenda) return agenda;
            return {
                id: agenda.pivot ? agenda.pivot.id : null,
                status: agenda.pivot ? agenda.pivot.status : 'ativo',
                agenda: agenda
            };
        });
    }
    return dadosInscricao;
};

const fetchDados = async () => {
  loading.value = true;
  try {
    const [planosResponse, inscricaoResponse, horariosResponse] = await Promise.all([
        axios.get('/api/planos'),
        axios.get(`/api/inscricoes/${props.id}`),
        axios.get('/api/horarios-agenda')
    ]);

    planos.value = planosResponse.data.data || planosResponse.data;

    const dataRaw = inscricaoResponse.data.data || inscricaoResponse.data;
    const dataAdaptada = adaptarEstruturaParaTemplate(dataRaw);
    inscricao.value = dataAdaptada;

    horariosAgenda.value = (horariosResponse.data.data || horariosResponse.data)
        .map(h => ({ ...h, ocupacao: h.ocupacao || 0 }))
        .sort((a, b) => a.dia_semana - b.dia_semana);

    Object.assign(form, {
        usuario_id: dataAdaptada.usuario_id,
        plano_id: dataAdaptada.plano_id,
        status: dataAdaptada.status,
        horarios_agenda_ids: dataAdaptada.horarios_aluno.map(h => h.agenda.id),
    });

  } catch (error) {
    console.error("Erro ao buscar dados:", error);
    errorMessage.value = "Erro ao carregar os dados.";
  } finally {
    loading.value = false;
  }
};

const toggleHorario = (id) => {
    if (isCancelada.value) return; // Bloqueio extra no clique

    const horario = horariosAgenda.value.find(h => h.id === id);
    const limite = limiteHorarios.value;

    if (!horario || horario.ocupacao >= horario.vagas_totais) return;

    const index = form.horarios_agenda_ids.indexOf(id);

    if (index > -1) {
        form.horarios_agenda_ids.splice(index, 1);
        errorMessage.value = '';
    } else {
        if (form.horarios_agenda_ids.length >= limite) {
            errorMessage.value = `O limite de horários fixos para o plano (${limite}x) foi atingido.`;
            return;
        }
        form.horarios_agenda_ids.push(id);
        errorMessage.value = '';
    }
};

const submitForm = async () => {
  if (isCancelada.value) return; // Segurança

  saving.value = true;
  errorMessage.value = '';
  successMessage.value = '';

  if (form.horarios_agenda_ids.length !== limiteHorarios.value) {
    errorMessage.value = `O plano exige a seleção de EXATAMENTE ${limiteHorarios.value} horários.`;
    saving.value = false;
    return;
  }

  const payload = {
      usuario_id: form.usuario_id,
      plano_id: form.plano_id,
      status: form.status,
      horarios_agenda_ids: form.horarios_agenda_ids,
  };

  try {
    const response = await axios.put(`/api/inscricoes/${props.id}`, payload);

    successMessage.value = 'Inscrição atualizada com sucesso!';

    const responseData = response.data.data || response.data;
    inscricao.value = adaptarEstruturaParaTemplate(responseData);

    setTimeout(() => {
      const userId = inscricao.value.usuario_id;
      router.push({
          name: 'listagem-inscricoes',
          query: userId ? { usuario_id: userId } : {}
      });
    }, 1500);

  } catch (error) {
    console.error("Erro ao atualizar:", error);
    if (error.response && error.response.status === 422) {
        errorMessage.value = error.response.data.message || 'Erro de validação.';
    } else {
        errorMessage.value = 'Falha crítica ao salvar.';
    }
  } finally {
    saving.value = false;
  }
};

const formatarDiaSemana = (dia) => {
  const dias = ['Dom', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
  return dias[dia === 7 ? 0 : dia];
};

onMounted(fetchDados);
</script>

<style scoped>
.form-input {
  @apply w-full p-3 rounded-lg bg-[#0f1616] text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-600 border border-transparent transition-all;
}
.form-section { @apply border border-gray-700 rounded-lg p-4 pt-2; }
.form-legend { @apply px-2 text-sm font-semibold text-teal-400 -ml-2; }
</style>
