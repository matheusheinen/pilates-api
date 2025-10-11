<template>
  <div class="bg-[#151515] p-6 rounded-xl text-white">
    <h3 class="text-lg font-semibold">Gestão de Horários da Agenda</h3>
    <p class="text-[#a0a0a0] mb-6">Defina todos os horários padrão em que o estúdio oferece aulas.</p>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-1 bg-[#242424] p-6 rounded-lg self-start">
        <form @submit.prevent="adicionarHorario" class="space-y-4">
          <h4 class="font-semibold text-white border-b border-gray-700 pb-2 mb-4">Adicionar Novo Horário</h4>

          <div v-if="mensagemErro" class="text-red-400 text-sm">{{ mensagemErro }}</div>

          <div>
            <label class="text-xs text-gray-400">Dia da Semana</label>
            <select v-model="novoHorario.dia_semana" class="form-input mt-1">
              <option value="1">Segunda-feira</option>
              <option value="2">Terça-feira</option>
              <option value="3">Quarta-feira</option>
              <option value="4">Quinta-feira</option>
              <option value="5">Sexta-feira</option>
              <option value="6">Sábado</option>
            </select>
          </div>

          <div>
            <label class="text-xs text-gray-400">Horário de Início</label>
            <input v-model="novoHorario.horario_inicio" type="time" step="1800" class="form-input mt-1">
          </div>

          <div>
            <label class="text-xs text-gray-400">Duração (minutos)</label>
            <input v-model="novoHorario.duracao_minutos" type="number" step="5" min="30" class="form-input mt-1">
          </div>

          <button type="submit" class="w-full py-2 rounded-lg bg-teal-700 hover:bg-teal-600 font-semibold transition-colors">
            Adicionar Horário
          </button>
        </form>
      </div>

      <div class="lg:col-span-2">
        <h4 class="font-semibold text-white mb-4">Horários Cadastrados</h4>
        <div class="space-y-2 max-h-[60vh] overflow-y-auto pr-2">
          <div v-if="horarios.length === 0" class="text-center py-8 text-[#a0a0a0]">
            Nenhum horário cadastrado.
          </div>
          <div v-for="horario in horarios" :key="horario.id" class="bg-[#242424] p-3 rounded-lg flex justify-between items-center">
            <div>
              <p class="font-medium">{{ formatarDiaSemana(horario.dia_semana) }} - {{ horario.horario_inicio.substring(0, 5) }}</p>
              <p :class="['text-sm', horario.inscricao ? 'text-green-400' : 'text-yellow-400']">
                {{ horario.inscricao ? `Ocupado por: ${horario.inscricao.usuario.nome}` : 'Livre' }}
              </p>
            </div>
            <button @click="removerHorario(horario.id)" class="text-red-500 hover:text-red-400 p-1 rounded-full hover:bg-red-500/10 transition-colors">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
            </button>
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
const novoHorario = reactive({
  dia_semana: '1',
  horario_inicio: '08:00',
  duracao_minutos: 50,
});
const mensagemErro = ref('');

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
  try {
    await axios.post('/api/horarios-agenda', novoHorario);
    await fetchHorarios();
  } catch (error) {
    mensagemErro.value = error.response?.data?.message || "Erro ao adicionar horário.";
  }
};

const removerHorario = async (id) => {
  if (confirm('Tem certeza que deseja remover este horário padrão?')) {
    try {
      await axios.delete(`/api/horarios-agenda/${id}`);
      await fetchHorarios();
    } catch (error) {
      alert(error.response?.data?.message || "Não foi possível remover o horário.");
    }
  }
};

const formatarDiaSemana = (dia) => {
  return ['','Segunda-feira','Terça-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sábado'][dia] || 'Inválido';
};

onMounted(fetchHorarios);
</script>

<style>
.form-input {
  @apply w-full p-2 rounded-lg bg-[#0f1616] text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-600;
}
</style>
