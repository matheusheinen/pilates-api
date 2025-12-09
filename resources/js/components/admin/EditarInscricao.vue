<template>
  <div v-if="loading" class="flex flex-col items-center justify-center py-20">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-teal-500 mb-4"></div>
      <p class="text-gray-400">Carregando inscrição...</p>
  </div>

  <div v-else-if="inscricao" class="space-y-6 animate-fade-in">

    <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
      <h2 class="text-2xl font-bold text-white">
        Editar: <span class="text-teal-500">{{ inscricao.usuario?.nome || 'N/A' }}</span>
      </h2>
      <router-link
        :to="{ name: 'admin-inscricoes', query: inscricao.usuario_id ? { usuario_id: inscricao.usuario_id } : {} }"
        class="text-sm font-semibold text-teal-500 hover:text-teal-400 flex items-center gap-1 transition-colors"
      >
        <span>&larr;</span> Voltar
      </router-link>
    </div>

    <div v-if="isCancelada" class="bg-red-900/20 border border-red-800 text-red-200 p-4 rounded-lg flex items-center gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
        <div>
            <strong>Inscrição Cancelada:</strong> Edição bloqueada. Para reativar este aluno, crie uma nova matrícula.
        </div>
    </div>

    <div v-if="feedback.message"
         :class="['p-4 rounded-lg text-sm border flex justify-between items-center',
         feedback.type === 'error' ? 'bg-red-900/20 border-red-800 text-red-200' : 'bg-green-900/20 border-green-800 text-green-200']">
      <span>{{ feedback.message }}</span>
      <button type="button" @click="feedback.message = ''" class="hover:text-white font-bold">&times;</button>
    </div>

    <form @submit.prevent="submitForm" class="bg-[#151515] p-6 md:p-8 rounded-xl border border-gray-800 shadow-xl space-y-8 relative">

      <div v-if="isCancelada" class="absolute inset-0 bg-[#151515]/60 z-10 cursor-not-allowed rounded-xl backdrop-blur-[1px]"></div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div>
            <label class="block text-xs uppercase tracking-wider text-gray-500 mb-2 font-semibold">Status</label>
            <select v-model="form.status" class="form-input" required :disabled="isCancelada">
                <option value="ativa">ATIVA</option>
                <option value="inativa">INATIVA (Suspensa)</option>
                <option value="trancada">TRANCADA (Pausa)</option>
                <option value="cancelada">CANCELADA (Encerrada)</option>
            </select>
          </div>

          <div>
            <label class="block text-xs uppercase tracking-wider text-gray-500 mb-2 font-semibold">Plano</label>
            <select v-model="form.plano_id" class="form-input" required :disabled="saving || isCancelada">
                <option v-for="plano in planos" :key="plano.id" :value="plano.id">
                    {{ plano.nome }} ({{ getLimiteDoPlano(plano.id) }}x/semana)
                </option>
            </select>
          </div>

          <div>
            <label class="block text-xs uppercase tracking-wider text-gray-500 mb-2 font-semibold">Data Início</label>
            <input :value="inscricao.data_inicio" type="date" class="form-input opacity-50 cursor-not-allowed" disabled />
          </div>
      </div>

      <div class="border-t border-gray-800 pt-6">
        <label class="block text-xs uppercase tracking-wider text-gray-500 mb-4 font-semibold">
            Horários Reservados ({{ form.horarios_agenda_ids.length }}/{{ limiteHorarios }})
        </label>

        <div class="bg-[#1a1a1a] rounded-lg border border-gray-800 overflow-hidden">

            <div class="flex overflow-x-auto border-b border-gray-800 bg-[#111] scrollbar-hide">
                <button
                  v-for="dia in diasSemana"
                  :key="dia.id"
                  type="button"
                  @click.prevent="trocarAba(dia.id)"
                  class="flex-1 px-4 py-3 text-sm font-medium whitespace-nowrap transition-all focus:outline-none border-b-2"
                  :class="abaAtiva === dia.id
                    ? 'border-teal-500 text-teal-400 bg-[#1a1a1a]'
                    : 'border-transparent text-gray-500 hover:text-gray-300 hover:bg-[#151515]'"
                >
                  {{ dia.nome }}
                  <span v-if="temSelecaoNoDia(dia.id)" class="ml-2 w-1.5 h-1.5 inline-block rounded-full bg-teal-500"></span>
                </button>
            </div>

            <div class="p-4 min-h-[250px] bg-[#1a1a1a]">

                <div v-if="horariosDaAba.length === 0" class="h-full flex flex-col items-center justify-center text-gray-500 py-12 border-2 border-dashed border-gray-800 rounded-lg">
                    <p>Sem horários disponíveis na {{ getNomeDia(abaAtiva) }}.</p>
                </div>

                <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 animate-fade-in-fast">
                    <div
                        v-for="horario in horariosDaAba"
                        :key="horario.id"
                        @click="!isCancelada && toggleHorario(horario)"
                        :class="getCardClasses(horario)"
                    >
                        <div class="flex items-center gap-3">
                             <div class="bg-[#0f0f0f] px-3 py-2 rounded text-lg font-mono font-bold border border-gray-800 shadow-sm"
                                  :class="isSelected(horario.id) ? 'text-teal-400' : 'text-gray-400'">
                                {{ formatarHora(horario.horario_inicio) }}
                             </div>
                             <div>
                                <div class="text-[10px] uppercase text-gray-500 font-bold">Vagas</div>
                                <div class="text-xs font-semibold" :class="horario.ocupacao >= horario.vagas_totais ? 'text-red-400' : 'text-gray-300'">
                                    {{ horario.ocupacao }}/{{ horario.vagas_totais }}
                                </div>
                             </div>
                        </div>

                        <div class="pointer-events-none">
                             <div class="w-6 h-6 rounded border flex items-center justify-center transition-all"
                                  :class="isSelected(horario.id) ? 'bg-teal-600 border-teal-600' : 'bg-[#111] border-gray-700'">
                                  <svg v-if="isSelected(horario.id)" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"></path></svg>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>

      <div class="flex justify-end pt-4 border-t border-gray-800">
        <button v-if="!isCancelada"
            type="submit"
            :disabled="saving"
            class="bg-teal-700 hover:bg-teal-600 text-white font-bold py-3 px-8 rounded-lg shadow-lg hover:shadow-teal-900/20 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
        >
          <span v-if="saving" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
          {{ saving ? 'Salvando...' : 'Salvar Alterações' }}
        </button>

        <router-link v-else :to="{ name: 'admin-inscricoes' }" class="text-gray-400 hover:text-white font-semibold py-3 px-8">
            Voltar
        </router-link>
      </div>

    </form>
  </div>

  <div v-else class="text-center text-red-500 mt-10">Inscrição não encontrada.</div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const props = defineProps({ id: { type: [String, Number], required: true } });
const router = useRouter();

// --- Estados ---
const loading = ref(true);
const saving = ref(false);
const inscricao = ref(null);
const planos = ref([]);
const horariosCache = ref([]); // Cache ordenado (performance)
const abaAtiva = ref(1);
const feedback = reactive({ message: '', type: '' });

// --- Constantes ---
const diasSemana = [
    { id: 1, nome: 'Segunda' }, { id: 2, nome: 'Terça' }, { id: 3, nome: 'Quarta' },
    { id: 4, nome: 'Quinta' }, { id: 5, nome: 'Sexta' }, { id: 6, nome: 'Sábado' }, { id: 7, nome: 'Domingo' }
];

const form = reactive({
    usuario_id: null,
    plano_id: null,
    status: 'ativa',
    horarios_agenda_ids: [],
});

// --- Computed ---
const isCancelada = computed(() => inscricao.value?.status === 'cancelada');

const limiteHorarios = computed(() => {
    if (!form.plano_id) return 0;
    const plano = planos.value.find(p => p.id === form.plano_id);
    return plano ? parseInt(plano.numero_aulas || 0) : 0;
});

// Filtro leve para a aba ativa
const horariosDaAba = computed(() => {
    return horariosCache.value.filter(h => h.dia_semana === abaAtiva.value);
});

// --- Helpers ---
const formatarHora = (h) => h ? h.substring(0, 5) : '--:--';
const getNomeDia = (id) => diasSemana.find(d => d.id === id)?.nome || 'Dia';
const isSelected = (id) => form.horarios_agenda_ids.includes(id);
const getLimiteDoPlano = (id) => planos.value.find(p => p.id === id)?.numero_aulas || 0;
const trocarAba = (id) => { abaAtiva.value = id; };

const temSelecaoNoDia = (diaId) => {
    // Verifica rapidamente se há IDs selecionados neste dia
    return horariosCache.value.some(h => h.dia_semana === diaId && isSelected(h.id));
};

const getCardClasses = (horario) => {
    if (isCancelada.value) return 'relative p-3 rounded-lg border flex justify-between items-center opacity-50 cursor-not-allowed bg-[#1a1a1a] border-gray-700';

    const lotado = horario.ocupacao >= horario.vagas_totais;
    const selecionado = isSelected(horario.id);

    // Se está lotado E eu não estou nele = Bloqueado
    if (lotado && !selecionado) {
        return 'relative p-3 rounded-lg border flex justify-between items-center bg-red-900/10 border-red-900/30 opacity-50 cursor-not-allowed';
    }

    if (selecionado) {
        return 'relative p-3 rounded-lg border flex justify-between items-center bg-teal-900/20 border-teal-500 cursor-pointer shadow-md';
    }

    return 'relative p-3 rounded-lg border flex justify-between items-center bg-[#1a1a1a] border-gray-700 hover:border-gray-500 cursor-pointer transition-all';
};

// --- Lógica de Negócio (Seleção) ---
const toggleHorario = (horario) => {
    if (isCancelada.value) return;

    // Se lotado e eu não estou nele, bloqueia clique
    if (horario.ocupacao >= horario.vagas_totais && !isSelected(horario.id)) return;

    const index = form.horarios_agenda_ids.indexOf(horario.id);
    const limite = limiteHorarios.value;

    if (index > -1) {
        // Remover
        form.horarios_agenda_ids.splice(index, 1);
        feedback.message = '';
    } else {
        // Adicionar (com trava de limite)
        if (form.horarios_agenda_ids.length >= limite) {
            feedback.message = `Limite do plano (${limite} aulas) atingido.`;
            feedback.type = 'error';
            return;
        }
        form.horarios_agenda_ids.push(horario.id);
        feedback.message = '';
    }
};

const adaptarEstrutura = (dados) => {
    // Garante que pegamos apenas os IDs dos horários que vieram da API
    if (dados.horarios_aluno && Array.isArray(dados.horarios_aluno)) {
        return dados.horarios_aluno
            .map(item => item.agenda ? item.agenda.id : (item.pivot ? item.id : null))
            .filter(id => id !== null);
    }
    return [];
};

// --- API ---
const fetchDados = async () => {
  loading.value = true;
  try {
    const [resPlanos, resInscricao, resHorarios] = await Promise.all([
        axios.get('/api/planos'),
        axios.get(`/api/inscricoes/${props.id}`),
        axios.get('/api/horarios-agenda')
    ]);

    planos.value = resPlanos.data.data || resPlanos.data;
    const inscricaoData = resInscricao.data.data || resInscricao.data;
    inscricao.value = inscricaoData;

    // Processamento Único e Pesado dos Horários (Para não travar no template)
    const rawHorarios = resHorarios.data.data || resHorarios.data;
    horariosCache.value = rawHorarios.map(h => ({
        ...h,
        dia_semana: parseInt(h.dia_semana),
        vagas_totais: parseInt(h.vagas_totais),
        ocupacao: parseInt(h.ocupacao || 0)
    })).sort((a, b) => a.horario_inicio.localeCompare(b.horario_inicio));

    // Preenche Form
    Object.assign(form, {
        usuario_id: inscricaoData.usuario_id,
        plano_id: inscricaoData.plano_id,
        status: inscricaoData.status,
        horarios_agenda_ids: adaptarEstrutura(inscricaoData),
    });

  } catch (error) {
    console.error(error);
    feedback.message = "Erro ao carregar dados.";
    feedback.type = 'error';
  } finally {
    loading.value = false;
  }
};

const submitForm = async () => {
  if (isCancelada.value) return;

  if (form.horarios_agenda_ids.length !== limiteHorarios.value) {
    feedback.message = `Selecione exatamente ${limiteHorarios.value} horário(s).`;
    feedback.type = 'error';
    return;
  }

  saving.value = true;
  try {
    const response = await axios.put(`/api/inscricoes/${props.id}`, form);

    // Atualiza estado local
    const dataRes = response.data.data || response.data;
    inscricao.value = dataRes;
    form.horarios_agenda_ids = adaptarEstrutura(dataRes);

    feedback.message = 'Inscrição atualizada com sucesso!';
    feedback.type = 'success';

    setTimeout(() => {
      router.push({ name: 'admin-inscricoes', query: { usuario_id: form.usuario_id } });
    }, 1500);

  } catch (error) {
    const msg = error.response?.data?.message || 'Erro ao salvar.';
    feedback.message = msg;
    feedback.type = 'error';
  } finally {
    saving.value = false;
  }
};

onMounted(fetchDados);
</script>

<style scoped>
.form-input {
  @apply w-full p-3 rounded-lg bg-[#0f0f0f] text-white border border-gray-700 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 outline-none transition-all;
}
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

.animate-fade-in { animation: fadeIn 0.5s ease-out; }
.animate-fade-in-fast { animation: fadeIn 0.3s ease-out; }

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(5px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
