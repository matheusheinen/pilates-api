<template>
  <div class="bg-[#151515] p-6 rounded-xl text-white">
    <h3 class="text-lg font-semibold">Gestão de Horários da Agenda</h3>
    <p class="text-[#a0a0a0] mb-6">Defina os horários padrão e quantas vagas cada um possui.</p>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-1 bg-[#242424] p-6 rounded-lg self-start border border-[#333]">
        <form @submit.prevent="adicionarHorario" class="space-y-4">
          <h4 class="font-semibold text-white border-b border-gray-700 pb-2 mb-4">Adicionar Novo Horário</h4>

          <div v-if="mensagemErro" class="p-3 bg-red-900/50 border border-red-800 text-red-200 rounded text-sm">
             {{ mensagemErro }}
          </div>

          <div>
            <label class="block text-xs text-gray-400 mb-1">Dia da Semana</label>
            <select v-model="novoHorario.dia_semana" class="w-full bg-[#1e1e1e] border border-[#444] text-white rounded p-2 text-sm focus:border-[#009088] focus:ring-1 focus:ring-[#009088] outline-none">
              <option value="1">Segunda-feira</option>
              <option value="2">Terça-feira</option>
              <option value="3">Quarta-feira</option>
              <option value="4">Quinta-feira</option>
              <option value="5">Sexta-feira</option>
              <option value="6">Sábado</option>
            </select>
          </div>

          <div>
            <label class="block text-xs text-gray-400 mb-1">Horário de Início</label>
            <input
              v-model="novoHorario.horario_inicio"
              type="time"
              class="w-full bg-[#1e1e1e] border border-[#444] text-white rounded p-2 text-sm focus:border-[#009088] focus:ring-1 focus:ring-[#009088] outline-none"
            >
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs text-gray-400 mb-1">Duração (min)</label>
              <input
                v-model="novoHorario.duracao_minutos"
                type="number"
                step="5"
                min="10"
                class="w-full bg-[#1e1e1e] border border-[#444] text-white rounded p-2 text-sm focus:border-[#009088] focus:ring-1 focus:ring-[#009088] outline-none"
              >
            </div>

            <div>
              <label class="block text-xs text-gray-400 mb-1">Total de Vagas</label>
              <input
                v-model="novoHorario.vagas_totais"
                type="number"
                min="1"
                max="10"
                class="w-full bg-[#1e1e1e] border border-[#444] text-white rounded p-2 text-sm focus:border-[#009088] focus:ring-1 focus:ring-[#009088] outline-none"
              >
            </div>
          </div>

          <button
            type="submit"
            class="w-full bg-[#009088] hover:bg-[#007972] text-white font-medium py-2 px-4 rounded transition duration-200 mt-4 shadow-lg flex justify-center items-center gap-2">
            <span>+</span> Adicionar Horário
          </button>
        </form>
      </div>

      <div class="lg:col-span-2">
        <div class="bg-[#242424] rounded-lg border border-[#333] overflow-hidden">
          <div v-if="horarios.length === 0" class="p-8 text-center text-gray-500">
            Nenhum horário cadastrado ainda.
          </div>

          <div v-else class="divide-y divide-[#333]">
            <div class="grid grid-cols-12 gap-4 p-3 bg-[#2a2a2a] text-xs text-gray-400 font-medium uppercase tracking-wider">
               <div class="col-span-3">Dia</div>
               <div class="col-span-2">Início</div>
               <div class="col-span-2">Duração</div>
               <div class="col-span-2">Vagas</div>
               <div class="col-span-3 text-right">Ações</div>
            </div>

            <div v-for="horario in horarios" :key="horario.id" class="grid grid-cols-12 gap-4 p-4 items-center hover:bg-[#2b2b2b] transition duration-150 text-sm">

              <div class="col-span-3 font-medium text-white">
                {{ formatarDiaSemana(horario.dia_semana) }}
              </div>

              <div class="col-span-2 text-gray-300 font-mono">
                {{ horario.horario_inicio.substring(0, 5) }}
              </div>

              <div class="col-span-2 text-gray-400">
                {{ horario.duracao_minutos }} min
              </div>

              <div class="col-span-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-900/30 text-blue-200 border border-blue-800">
                   {{ horario.inscricoes_count || 0 }} / {{ horario.vagas_totais }}
                </span>
              </div>

              <div class="col-span-3 text-right">
                 <button
                    @click="removerHorario(horario.id)"
                    class="text-red-400 hover:text-red-300 hover:bg-red-900/20 p-1.5 rounded transition"
                    title="Remover Horário">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                 </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

const horarios = ref([]);
const mensagemErro = ref('');

// Objeto reativo do formulário
const novoHorario = reactive({
  dia_semana: '1',
  horario_inicio: '08:00',
  duracao_minutos: 50,
  vagas_totais: 3 // Valor padrão inicial
});

const fetchHorarios = async () => {
  try {
    const response = await axios.get('/api/horarios-agenda');
    horarios.value = response.data;
  } catch (error) {
    console.error("Erro ao buscar horários:", error);
  }
};

const adicionarHorario = async () => {
  mensagemErro.value = '';

  // Validação simples no front antes de enviar
  if (novoHorario.vagas_totais < 1) {
      mensagemErro.value = "O número de vagas deve ser pelo menos 1.";
      return;
  }

  try {
    await axios.post('/api/horarios-agenda', novoHorario);
    await fetchHorarios();
    // Resetar apenas horário para facilitar cadastro em sequência, mantém dia e vagas
    // novoHorario.horario_inicio = '';
    alert("Horário cadastrado com sucesso!");
  } catch (error) {
    if (error.response && error.response.data && error.response.data.message) {
        mensagemErro.value = error.response.data.message;
    } else {
        mensagemErro.value = "Erro ao adicionar horário. Verifique os dados.";
    }
  }
};

const removerHorario = async (id) => {
  if (!confirm('Tem certeza que deseja remover este horário?')) return;

  try {
    await axios.delete(`/api/horarios-agenda/${id}`);
    await fetchHorarios();
  } catch (error) {
    alert(error.response?.data?.message || "Não foi possível remover o horário.");
  }
};

const formatarDiaSemana = (dia) => {
  const dias = ['','Segunda-feira','Terça-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sábado','Domingo'];
  return dias[dia] || dia;
};

onMounted(() => {
  fetchHorarios();
});
</script>
