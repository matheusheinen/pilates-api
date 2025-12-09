<template>
  <div v-if="loading" class="text-center text-gray-400 mt-10">Carregando dados para a matrícula...</div>
  <div v-else-if="cliente" class="space-y-6">

    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-white">Nova Matrícula para: <span class="text-teal-500">{{ cliente.nome }}</span></h2>
      <router-link :to="{ name: 'detalhes-cliente', params: { id: props.id } }" class="text-sm font-semibold text-teal-500 hover:text-teal-400 flex items-center gap-1">
        <span>&larr;</span> Voltar
      </router-link>
    </div>

    <div v-if="successMessage" class="mb-4 bg-green-500/20 border border-green-700 text-green-300 text-sm p-3 rounded-lg">
      {{ successMessage }}
    </div>
    <div v-if="errorMessage" class="mb-4 bg-red-500/20 border border-red-700 text-red-300 text-sm p-3 rounded-lg flex justify-between items-center">
      <span>{{ errorMessage }}</span>
      <button @click="errorMessage = ''" class="text-red-300 hover:text-white">&times;</button>
    </div>

    <form @submit.prevent="submitMatricula" class="bg-[#151515] p-8 rounded-xl border border-white/10 space-y-6">

      <fieldset class="form-section border-gray-700">
        <legend class="form-legend">Contrato e Plano</legend>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

          <div>
            <label class="block text-xs text-gray-400 mb-1 ml-1">Selecione o Plano *</label>
            <select v-model="form.plano_id" class="form-input" required>
              <option value="" disabled>Escolha um plano...</option>
              <option v-for="plano in planos" :key="plano.id" :value="plano.id">
                {{ plano.nome }} ({{ plano.numero_aulas }} aulas/semana) - R$ {{ formatarPreco(plano.preco) }}
              </option>
            </select>
            <p v-if="limiteHorarios > 0" class="text-xs text-teal-400 mt-1">
                Limite de horários fixos semanais: <strong>{{ limiteHorarios }}</strong>
            </p>
            <p v-else class="text-xs text-gray-500 mt-1 italic">Selecione um plano para liberar a escolha de horários.</p>
          </div>

          <div>
            <label class="block text-xs text-gray-400 mb-1 ml-1">Data de Início *</label>
            <input v-model="form.data_inicio" type="date" class="form-input" required :min="minDate" />
          </div>
        </div>
      </fieldset>

      <fieldset class="form-section border-gray-700 p-0 overflow-hidden">
        <legend class="form-legend ml-4 mt-2">Horários Fixos (Vagas)</legend>

        <div class="bg-[#1a1a1a] border-t border-gray-700 mt-2">

            <div class="flex overflow-x-auto border-b border-gray-700 bg-[#1e1e1e] scrollbar-hide">
                <button
                  v-for="dia in diasAbas"
                  :key="dia.id"
                  @click.prevent="abaAtiva = dia.id"
                  type="button"
                  class="px-5 py-3 text-sm font-semibold whitespace-nowrap transition-colors border-b-2 flex-1 hover:bg-[#252525] focus:outline-none"
                  :class="abaAtiva === dia.id ? 'border-teal-500 text-teal-400 bg-[#2a2a2a]' : 'border-transparent text-gray-400'"
                >
                  {{ dia.nome }}
                  <span v-if="temSelecaoNoDia(dia.id)" class="ml-2 w-2 h-2 inline-block rounded-full bg-teal-500"></span>
                </button>
            </div>

            <div class="p-4 bg-[#242424] min-h-[200px]">

                <div v-if="!horariosFiltrados.length" class="flex flex-col items-center justify-center text-gray-500 py-10 border border-dashed border-gray-700 rounded-lg h-full">
                     <p>Nenhum horário cadastrado para {{ getNomeDia(abaAtiva) }}.</p>
                </div>

                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    <div
                        v-for="horario in horariosFiltrados"
                        :key="horario.id"
                        @click="toggleHorario(horario.id)"
                        :class="['relative p-3 rounded-lg border transition-all duration-200 flex justify-between items-center group select-none',
                            // Lógica de Cores e Cursor (Mantida a lógica original, apenas adaptada ao novo layout)
                            horario.ocupacao >= horario.vagas_totais
                                ? 'bg-red-900/10 border-red-900/30 opacity-60 cursor-not-allowed'
                                : (form.horarios_agenda_ids.includes(horario.id)
                                    ? 'bg-teal-900/20 border-teal-500 cursor-pointer shadow-md shadow-teal-900/10'
                                    : 'bg-[#1a1a1a] border-gray-700 hover:border-gray-500 cursor-pointer')
                        ]"
                    >
                        <div class="flex items-center gap-3">
                             <div class="bg-[#151515] px-3 py-2 rounded text-lg font-mono font-bold border border-gray-800 shadow-inner"
                                  :class="form.horarios_agenda_ids.includes(horario.id) ? 'text-teal-400' : 'text-gray-300'">
                                {{ horario.horario_inicio?.substring(0, 5) }}
                             </div>
                             <div>
                                <div class="text-[10px] uppercase text-gray-500 font-bold mb-0.5">Vagas</div>
                                <div class="text-xs font-medium" :class="horario.ocupacao >= horario.vagas_totais ? 'text-red-400' : 'text-gray-300'">
                                    {{ horario.ocupacao }} / {{ horario.vagas_totais }}
                                    <span v-if="horario.ocupacao >= horario.vagas_totais" class="text-[10px] ml-1 text-red-500 font-bold uppercase">(Esgotado)</span>
                                </div>
                             </div>
                        </div>

                        <div class="pointer-events-none">
                             <div class="w-6 h-6 rounded border flex items-center justify-center transition-colors"
                                  :class="[
                                    form.horarios_agenda_ids.includes(horario.id) ? 'bg-teal-600 border-teal-600' : 'bg-[#151515] border-gray-600 group-hover:border-gray-400',
                                    (horario.ocupacao >= horario.vagas_totais && !form.horarios_agenda_ids.includes(horario.id)) ? 'opacity-0' : ''
                                  ]">
                                  <svg v-if="form.horarios_agenda_ids.includes(horario.id)" class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </fieldset>

      <div class="pt-4 border-t border-gray-700 flex justify-end">
        <button type="submit" :disabled="saving || form.horarios_agenda_ids.length !== limiteHorarios"
            :class="[form.horarios_agenda_ids.length !== limiteHorarios ? 'bg-gray-600' : 'bg-teal-700 hover:bg-teal-600']"
            class="text-white font-semibold py-3 px-8 rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-teal-900/20">
          {{ saving ? 'Processando Matrícula...' : `Confirmar Matrícula (${form.horarios_agenda_ids.length}/${limiteHorarios})` }}
        </button>
      </div>

    </form>
  </div>
  <div v-else class="text-center text-red-400 mt-10">Erro: Cliente ou dados não encontrados.</div>
</template>

<script setup>
import { ref, onMounted, reactive, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const props = defineProps({
  id: {
    type: [String, Number],
    required: true
  }
});

const router = useRouter();

// --- Estados ---
const loading = ref(true);
const saving = ref(false);
const cliente = ref(null);
const planos = ref([]);
const horariosAgenda = ref([]); // Lista completa vinda da API
const errorMessage = ref('');
const successMessage = ref('');

// --- NOVO: Configuração das Abas (Visual apenas) ---
const abaAtiva = ref(1); // Inicia na Segunda-feira (1)
const diasAbas = [
    { id: 1, nome: 'Segunda' },
    { id: 2, nome: 'Terça' },
    { id: 3, nome: 'Quarta' },
    { id: 4, nome: 'Quinta' },
    { id: 5, nome: 'Sexta' },
    { id: 6, nome: 'Sábado' },
    { id: 7, nome: 'Domingo' }
];

// --- Formulário (Estrutura original) ---
const form = reactive({
  usuario_id: props.id,
  plano_id: '',
  data_inicio: new Date().toISOString().split('T')[0],
  horarios_agenda_ids: [],
});

const minDate = computed(() => new Date().toISOString().split('T')[0]);

// --- Computed: Limites (Logica original) ---
const limiteHorarios = computed(() => {
    const planoSelecionado = planos.value.find(p => p.id === form.plano_id);
    if (!planoSelecionado || !planoSelecionado.numero_aulas) return 0;
    const limite = parseInt(planoSelecionado.numero_aulas);
    return limite > 0 ? limite : 0;
});

// --- NOVO: Filtros para a Aba Ativa ---
const horariosFiltrados = computed(() => {
    // Filtra a lista completa baseada na aba selecionada
    return horariosAgenda.value
        .filter(h => parseInt(h.dia_semana) === abaAtiva.value)
        .sort((a, b) => a.horario_inicio.localeCompare(b.horario_inicio));
});

const getNomeDia = (id) => {
    const dia = diasAbas.find(d => d.id === id);
    return dia ? dia.nome : 'Dia Selecionado';
};

const temSelecaoNoDia = (diaId) => {
    return horariosAgenda.value.some(h =>
        parseInt(h.dia_semana) === diaId && form.horarios_agenda_ids.includes(h.id)
    );
};

// --- Carregamento de Dados (Função original mantida) ---
const fetchData = async () => {
  loading.value = true;
  try {
    const clienteResponse = await axios.get(`/api/usuarios/${props.id}`);
    cliente.value = clienteResponse.data.data || clienteResponse.data;

    const planosResponse = await axios.get(`/api/planos`);
    planos.value = planosResponse.data.data || planosResponse.data;

    const horariosResponse = await axios.get(`/api/horarios-agenda`);
    horariosAgenda.value = (horariosResponse.data.data || horariosResponse.data)
        .map(h => ({
            ...h,
            ocupacao: h.ocupacao || 0,
            vagas_totais: parseInt(h.vagas_totais)
        }));

  } catch (error) {
    console.error("Erro ao buscar dados iniciais:", error);
    errorMessage.value = "Erro ao carregar dados do servidor.";
  } finally {
    loading.value = false;
  }
};

const formatarPreco = (preco) => {
  return parseFloat(preco).toFixed(2).replace('.', ',');
};

// --- Lógica de Interação (Mantida, apenas o trigger mudou) ---
const toggleHorario = (id) => {
    const horario = horariosAgenda.value.find(h => h.id === id);

    // 1. Validação de Vaga
    if (!horario || horario.ocupacao >= horario.vagas_totais) {
        return;
    }

    const limite = limiteHorarios.value;

    // 2. Validação de Plano Selecionado (Importante!)
    if (limite === 0) {
        errorMessage.value = 'Selecione um Plano antes de escolher os horários.';
        return;
    }

    const index = form.horarios_agenda_ids.indexOf(id);

    if (index > -1) {
        // Remover seleção
        form.horarios_agenda_ids.splice(index, 1);
        errorMessage.value = '';
    } else {
        // Adicionar seleção com verificação de limite
        if (form.horarios_agenda_ids.length >= limite) {
            errorMessage.value = `Limite de horários (${limite}) atingido. Desmarque um para trocar.`;
            return;
        }
        form.horarios_agenda_ids.push(id);
        errorMessage.value = '';
    }
};

// --- Envio (Lógica original mantida) ---
const submitMatricula = async () => {
  saving.value = true;
  errorMessage.value = '';
  successMessage.value = '';

  if (form.horarios_agenda_ids.length !== limiteHorarios.value) {
    errorMessage.value = `O plano exige a seleção de EXATAMENTE ${limiteHorarios.value} horário(s).`;
    saving.value = false;
    return;
  }

  const payload = {
    usuario_id: props.id,
    plano_id: form.plano_id,
    data_inicio: form.data_inicio,
    horarios_agenda_ids: form.horarios_agenda_ids
  };

  try {
    await axios.post('/api/inscricoes', payload);

    successMessage.value = 'Matrícula realizada com sucesso! Redirecionando...';

    setTimeout(() => {
      router.push({ name: 'detalhes-cliente', params: { id: props.id } });
    }, 1500);

  } catch (error) {
    console.error("Erro ao realizar matrícula:", error);
    if (error.response && error.response.status === 422) {
        errorMessage.value = error.response.data.message || 'Erro de validação.';
    } else {
        errorMessage.value = 'Ocorreu um erro ao processar a matrícula.';
    }
  } finally {
    saving.value = false;
  }
};

onMounted(fetchData);
</script>

<style scoped>
.form-input {
  @apply w-full p-3 rounded-lg bg-[#0f1616] text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-600 border border-transparent transition-all;
}
.form-section { @apply border border-gray-700 rounded-lg p-4 pt-2; }
.form-legend { @apply px-2 text-sm font-semibold text-teal-400 -ml-2; }
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>
