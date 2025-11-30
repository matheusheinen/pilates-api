<template>
  <div class="bg-[#151515] p-6 rounded-xl text-white">
    <h3 class="text-lg font-semibold">Gestão de Horários da Agenda</h3>
    <p class="text-[#a0a0a0] mb-6">Defina os horários padrão e quantas vagas cada um possui.</p>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

      <div class="lg:col-span-1 bg-[#242424] p-6 rounded-xl self-start border border-gray-700 shadow-md">
        <form @submit.prevent="salvarHorario" class="space-y-4">
          <h4 class="font-semibold text-white border-b border-gray-600 pb-2 mb-4">
              {{ modoEdicao ? 'Editar Horário' : 'Adicionar Novo Horário' }}
          </h4>

          <div v-if="mensagemErro" class="p-3 bg-red-900/50 border border-red-800 text-red-200 rounded text-sm">
             {{ mensagemErro }}
          </div>

          <div>
            <label class="block text-xs text-gray-400 mb-1">Dia da Semana</label>
            <select v-model="form.dia_semana" class="form-input-style" required>
              <option value="" disabled>Selecione o dia...</option>
              <option value="1">Segunda-feira</option>
              <option value="2">Terça-feira</option>
              <option value="3">Quarta-feira</option>
              <option value="4">Quinta-feira</option>
              <option value="5">Sexta-feira</option>
              <option value="6">Sábado</option>
              <option value="7">Domingo</option>
            </select>
          </div>

          <div>
            <label class="block text-xs text-gray-400 mb-1">Horário de Início</label>
            <input v-model="form.horario_inicio" type="time" class="form-input-style" required>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs text-gray-400 mb-1">Duração (min)</label>
              <input v-model.number="form.duracao_minutos" type="number" step="5" min="10" class="form-input-style" required>
            </div>

            <div>
              <label class="block text-xs text-gray-400 mb-1">Total de Vagas</label>
              <input v-model.number="form.vagas_totais" type="number" min="1" class="form-input-style" required>
            </div>
          </div>

          <div class="flex gap-2 pt-2">
              <button v-if="modoEdicao" type="button" @click="cancelarEdicao" class="flex-1 bg-gray-600 hover:bg-gray-500 text-white font-semibold py-2 rounded-lg transition-colors text-sm">
                Cancelar
              </button>
              <button type="submit" class="flex-1 bg-teal-700 hover:bg-teal-600 text-white font-semibold py-2 rounded-lg transition-colors">
                {{ modoEdicao ? 'Atualizar' : 'Cadastrar' }}
              </button>
          </div>
        </form>
      </div>

      <div class="lg:col-span-2">
        <div class="bg-[#242424] rounded-xl border border-gray-700 overflow-hidden shadow-md">
          <div v-if="!horarios.length" class="p-8 text-center text-gray-500">
              Nenhum horário ativo cadastrado.
          </div>

          <div v-else class="divide-y divide-gray-700">
            <div class="grid grid-cols-12 gap-2 p-3 bg-[#2a2a2a] text-xs text-gray-400 font-medium uppercase tracking-wider">
               <div class="col-span-3">Dia / Hora</div>
               <div class="col-span-2">Duração</div>
               <div class="col-span-3 text-center">Ocupação</div>
               <div class="col-span-4 text-right">Ações</div>
            </div>

            <div v-for="horario in horarios" :key="horario.id" class="grid grid-cols-12 items-center gap-2 py-3 px-3 hover:bg-[#2b2b2b] transition duration-150 text-sm">

              <div class="col-span-3 font-medium">
                <p class="text-white">{{ formatarDiaSemana(horario.dia_semana) }}</p>
                <p class="text-teal-400 text-xs font-mono">{{ horario.horario_inicio?.substring(0, 5) || 'N/A' }}</p>
              </div>

              <div class="col-span-2 text-gray-300">
                {{ horario.duracao_minutos }} min
              </div>

              <div class="col-span-3 text-center">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="horario.ocupacao >= horario.vagas_totais ? 'bg-red-900/30 text-red-200 border border-red-800' : 'bg-green-900/30 text-green-200 border border-green-800'">
                   {{ horario.ocupacao || 0 }} / {{ horario.vagas_totais }}
                </span>
              </div>

              <div class="col-span-4 text-right flex justify-end gap-2">
                 <button
                    @click="iniciarEdicao(horario)"
                    class="text-blue-400 hover:text-blue-300 bg-blue-900/20 hover:bg-blue-900/40 px-2 py-1 rounded transition font-semibold text-xs flex items-center"
                    title="Editar Horário">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                    Editar
                 </button>

                 <button
                    v-if="Number(horario.ocupacao) === 0"
                    @click="deletarHorario(horario.id)"
                    class="text-red-400 hover:text-red-300 bg-red-900/20 hover:bg-red-900/40 px-2 py-1 rounded transition font-semibold text-xs flex items-center"
                    title="Excluir Horário">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    Excluir
                 </button>

                 <span v-else class="text-gray-600 text-[10px] border border-gray-700 px-2 py-1 rounded cursor-not-allowed flex items-center" title="Não é possível excluir horários com alunos matriculados">
                    Ocupado
                 </span>
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
const modoEdicao = ref(false); // Controla se estamos editando ou criando

// Objeto único para o formulário (serve para criar e editar)
const form = reactive({
  id: null,
  dia_semana: 1,
  horario_inicio: '10:00',
  duracao_minutos: 50,
  vagas_totais: 3,
  status: 'ativo'
});

const fetchHorarios = async () => {
  try {
    const response = await axios.get('/api/horarios-agenda');
    horarios.value = response.data.data || response.data;
  } catch (error) {
    console.error("Erro ao buscar horários:", error);
    mensagemErro.value = "Erro ao buscar horários. Verifique a conexão com a API.";
  }
};

// Preenche o formulário com os dados do horário clicado
const iniciarEdicao = (horario) => {
    modoEdicao.value = true;
    form.id = horario.id;
    form.dia_semana = horario.dia_semana;
    form.horario_inicio = horario.horario_inicio; // Formato HH:mm:ss
    form.duracao_minutos = horario.duracao_minutos;
    form.vagas_totais = horario.vagas_totais;
    form.status = horario.status;
    mensagemErro.value = '';
};

// Limpa o formulário
const cancelarEdicao = () => {
    modoEdicao.value = false;
    form.id = null;
    form.dia_semana = 1;
    form.horario_inicio = '10:00';
    form.duracao_minutos = 50;
    form.vagas_totais = 3;
    form.status = 'ativo';
    mensagemErro.value = '';
};

const salvarHorario = async () => {
  mensagemErro.value = '';

  if (form.vagas_totais < 1) {
      mensagemErro.value = "O número de vagas deve ser pelo menos 1.";
      return;
  }

  const payload = { ...form, dia_semana: parseInt(form.dia_semana) };

  try {
    if (modoEdicao.value) {
        // UPDATE
        await axios.put(`/api/horarios-agenda/${form.id}`, payload);
        alert("Horário atualizado com sucesso!");
        modoEdicao.value = false; // Sai do modo edição
    } else {
        // CREATE
        await axios.post('/api/horarios-agenda', payload);
        alert("Horário cadastrado com sucesso!");
    }

    // Reseta form e recarrega lista
    cancelarEdicao();
    await fetchHorarios();

  } catch (error) {
    if (error.response && error.response.data && error.response.data.message) {
        mensagemErro.value = error.response.data.message;
    } else {
        mensagemErro.value = "Erro ao salvar horário. Verifique os dados.";
    }
  }
};

// Exclusão Condicional
const deletarHorario = async (id) => {
    if (!confirm('Tem certeza que deseja excluir este horário permanentemente?')) return;

    try {
        await axios.delete(`/api/horarios-agenda/${id}`);
        await fetchHorarios();
        alert("Horário excluído com sucesso.");
    } catch (error) {
        console.error("Erro ao excluir:", error);
        alert("Não foi possível excluir o horário. Tente novamente.");
    }
};

// Funções de Utilitário
const formatarDiaSemana = (dia) => {
  const dias = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
  return dias[dia === 7 ? 0 : dia];
};

onMounted(fetchHorarios);
</script>

<style scoped>
.form-input-style {
    @apply w-full bg-[#1e1e1e] border border-[#444] text-white rounded p-2 text-sm focus:border-teal-600 focus:ring-1 focus:ring-teal-600 outline-none;
}
</style>
