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
    <div v-if="errorMessage" class="mb-4 bg-red-500/20 border border-red-700 text-red-300 text-sm p-3 rounded-lg">
      {{ errorMessage }}
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
                Limite de horários fixos semanais: {{ limiteHorarios }}
            </p>
          </div>

          <div>
            <label class="block text-xs text-gray-400 mb-1 ml-1">Data de Início *</label>
            <input v-model="form.data_inicio" type="date" class="form-input" required :min="minDate" />
          </div>
        </div>
      </fieldset>

      <fieldset class="form-section border-gray-700">
        <legend class="form-legend">Horários Fixos (Vagas)</legend>
        <p class="text-sm text-gray-400 mb-4">Selecione os horários que o aluno ocupará.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">

          <div
            v-for="horario in horariosAgenda"
            :key="horario.id"
            @click="toggleHorario(horario.id)"

            :class="['p-4 rounded-lg transition-all duration-200',
                     // Desativa se Lotado OU (Limite Atingido E não selecionado)
                     horario.ocupacao >= horario.vagas_totais || (form.horarios_agenda_ids.length >= limiteHorarios && !form.horarios_agenda_ids.includes(horario.id)) ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:border-teal-600',

                     // Estilo de Lotado
                     horario.ocupacao >= horario.vagas_totais ? 'bg-red-900/40 border border-red-700' : 'border',

                     // Estilo de Selecionado
                     form.horarios_agenda_ids.includes(horario.id) ? 'bg-teal-700 border-teal-500 shadow-lg shadow-teal-900/30' : 'bg-[#1a1a1a] border-gray-700']"

            :title="horario.ocupacao >= horario.vagas_totais ? 'Vaga Esgotada' : (form.horarios_agenda_ids.length >= limiteHorarios && limiteHorarios > 0 && !form.horarios_agenda_ids.includes(horario.id) ? `Limite do Plano (${limiteHorarios}) atingido` : 'Clique para selecionar')"
          >
            <div class="flex justify-between items-start">
                <p :class="['font-semibold text-lg', horario.ocupacao >= horario.vagas_totais ? 'text-gray-300' : 'text-white']">
                    {{ getDiaSemana(horario.dia_semana) }}
                </p>
                <input
                    type="checkbox"
                    :checked="form.horarios_agenda_ids.includes(horario.id)"

                    :disabled="horario.ocupacao >= horario.vagas_totais || (form.horarios_agenda_ids.length >= limiteHorarios && !form.horarios_agenda_ids.includes(horario.id))"

                    class="h-5 w-5 rounded transition-colors duration-200"
                    :class="[horario.ocupacao >= horario.vagas_totais || (form.horarios_agenda_ids.length >= limiteHorarios && !form.horarios_agenda_ids.includes(horario.id)) ? 'bg-gray-600 cursor-not-allowed' : 'text-teal-600 bg-gray-700 border-gray-600 focus:ring-teal-500']"
                />
            </div>

            <p class="text-xl font-bold mt-1">{{ horario.horario_inicio?.slice(0, 5) || 'N/A' }}</p>

            <div class="mt-2 text-xs">
                <p :class="[horario.ocupacao >= horario.vagas_totais ? 'text-red-300' : 'text-gray-300']">
                    Vagas: {{ horario.ocupacao }} / {{ horario.vagas_totais }}
                </p>
                <p v-if="horario.ocupacao >= horario.vagas_totais" class="text-red-300 font-semibold mt-1">LOTADO</p>
            </div>
          </div>

        </div>
        <p v-if="!horariosAgenda.length" class="text-center text-gray-500 py-4">Nenhum horário disponível cadastrado.</p>
      </fieldset>

      <div class="pt-4 border-t border-gray-700 flex justify-end">
        <button type="submit" :disabled="saving || form.horarios_agenda_ids.length !== limiteHorarios"
            :class="[form.horarios_agenda_ids.length !== limiteHorarios ? 'bg-gray-600' : 'bg-teal-700 hover:bg-teal-600']"
            class="text-white font-semibold py-3 px-8 rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-teal-900/20">
          {{ saving ? 'Processando Matrícula...' : 'Confirmar Matrícula' }}
        </button>
      </div>

    </form>
  </div>
  <div v-else class="text-center text-red-400 mt-10">Erro: Cliente ou Matrícula não encontrada.</div>
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

// Variáveis de Estado
const loading = ref(true);
const saving = ref(false);
const cliente = ref(null);
const planos = ref([]);
const horariosAgenda = ref([]);
const errorMessage = ref('');
const successMessage = ref('');

// Dados do Formulário
const form = reactive({
  usuario_id: props.id,
  plano_id: '',
  data_inicio: new Date().toISOString().split('T')[0],
  horarios_agenda_ids: [],
});

const minDate = computed(() => new Date().toISOString().split('T')[0]);

// --- PROPRIEDADE COMPUTADA PARA O LIMITE (SOLUÇÃO DEFINITIVA) ---
const limiteHorarios = computed(() => {
    const planoSelecionado = planos.value.find(p => p.id === form.plano_id);

    if (!planoSelecionado || !planoSelecionado.numero_aulas) {
        return 0;
    }

    // SOLUÇÃO: Assumir que numero_aulas É o limite semanal (1, 2, ou 3)
    const limite = parseInt(planoSelecionado.numero_aulas);

    // Retorna o valor direto (ex: 2 para 2x/semana)
    return limite > 0 ? limite : 0;
});


// --- FUNÇÕES DE BUSCA DE DADOS ---

const fetchData = async () => {
  loading.value = true;
  try {
    // 1. Cliente
    const clienteResponse = await axios.get(`/api/usuarios/${props.id}`);
    cliente.value = clienteResponse.data.data || clienteResponse.data;

    // 2. Planos
    const planosResponse = await axios.get(`/api/planos`);
    planos.value = planosResponse.data.data || planosResponse.data;

    // 3. Horários da Agenda (Slot fixo)
    const horariosResponse = await axios.get(`/api/horarios-agenda`);

    horariosAgenda.value = (horariosResponse.data.data || horariosResponse.data)
        .map(h => ({
            ...h,
            ocupacao: h.ocupacao || 0
        }))
        // Ordena por dia da semana e hora
        .sort((a, b) => {
            if (a.dia_semana !== b.dia_semana) {
                return a.dia_semana - b.dia_semana;
            }
            return a.horario_inicio.localeCompare(b.horario_inicio);
        });

  } catch (error) {
    console.error("Erro ao buscar dados iniciais:", error);
    errorMessage.value = "Erro ao carregar planos ou horários. Verifique a API.";
  } finally {
    loading.value = false;
  }
};

// --- FUNÇÕES DE UTILIDADE ---

const getDiaSemana = (dia) => {
  const dias = ['Dom', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
  return dias[dia === 7 ? 0 : dia];
};

const formatarPreco = (preco) => {
  return parseFloat(preco).toFixed(2).replace('.', ',');
};

const toggleHorario = (id) => {
    const horario = horariosAgenda.value.find(h => h.id === id);
    const limite = limiteHorarios.value;

    // 1. Checa se o horário existe ou está lotado
    if (!horario || horario.ocupacao >= horario.vagas_totais) {
        return;
    }

    const index = form.horarios_agenda_ids.indexOf(id);

    if (index > -1) {
        // Remove
        form.horarios_agenda_ids.splice(index, 1);
        errorMessage.value = '';
    } else {
        // Checa se o limite do plano foi atingido
        if (form.horarios_agenda_ids.length >= limite) {
            errorMessage.value = `O limite de horários fixos para o plano (${limite} vaga(s) semanal(is)) foi atingido.`;
            return; // Bloqueia a adição
        }
        form.horarios_agenda_ids.push(id); // Adiciona
        errorMessage.value = '';
    }
};

// --- FUNÇÃO DE SUBMISSÃO ---

const submitMatricula = async () => {
  saving.value = true;
  errorMessage.value = '';
  successMessage.value = '';

  // Validação: Garante que o número de horários selecionados bate com o limite do plano
  if (form.horarios_agenda_ids.length !== limiteHorarios.value) {
    errorMessage.value = `Selecione exatamente ${limiteHorarios.value} horário(s) para o plano selecionado.`;
    saving.value = false;
    return;
  }

  const payload = {
    usuario_id: props.id, // CRÍTICO: ID do aluno
    plano_id: form.plano_id,
    data_inicio: form.data_inicio,
    horarios_agenda_ids: form.horarios_agenda_ids // CRÍTICO: Horários fixos
  };

  try {
    await axios.post('/api/inscricoes', payload);

    successMessage.value = 'Matrícula realizada e agenda gerada com sucesso! Redirecionando...';

    setTimeout(() => {
      // Redireciona para os detalhes do cliente
      router.push({ name: 'detalhes-cliente', params: { id: props.id } });
    }, 2000);

  } catch (error) {
    console.error("Erro ao realizar matrícula:", error);
    if (error.response && error.response.status === 422) {
        // Exibe o erro de vaga ou validação
        const message = error.response.data.message || 'Erro de validação.';
        errorMessage.value = message;
    } else {
        errorMessage.value = 'Ocorreu um erro inesperado. Tente novamente mais tarde.';
    }
  } finally {
    saving.value = false;
  }
};

onMounted(fetchData);
</script>

<style scoped>
/* Estilos mantidos */
.form-input {
  @apply w-full p-3 rounded-lg bg-[#0f1616] text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-600 border border-transparent transition-all;
}
.form-section { @apply border border-gray-700 rounded-lg p-4 pt-2; }
.form-legend { @apply px-2 text-sm font-semibold text-teal-400 -ml-2; }
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
