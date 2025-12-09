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
        <div class="bg-[#242424] rounded-xl border border-gray-700 overflow-hidden shadow-md flex flex-col h-full min-h-[400px]">

          <div class="flex overflow-x-auto border-b border-gray-700 bg-[#1e1e1e] scrollbar-hide">
            <button
              v-for="dia in diasAbas"
              :key="dia.id"
              @click="abaAtiva = dia.id"
              class="px-5 py-3 text-sm font-semibold whitespace-nowrap transition-colors border-b-2 flex-1 hover:bg-[#252525]"
              :class="abaAtiva === dia.id ? 'border-teal-500 text-teal-400 bg-[#2a2a2a]' : 'border-transparent text-gray-400'"
            >
              {{ dia.nome }}
            </button>
          </div>

          <div class="p-4 flex-1 bg-[#242424]">

            <div v-if="!horariosFiltrados.length" class="h-full flex flex-col items-center justify-center text-gray-500 py-10 border-2 border-dashed border-gray-800 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mb-2 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                <p>Nenhum horário cadastrado para {{ getNomeDia(abaAtiva) }}.</p>
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div
                v-for="horario in horariosFiltrados"
                :key="horario.id"
                class="flex items-center justify-between bg-[#1a1a1a] p-3 rounded-lg border border-gray-700 hover:border-teal-900/50 transition group"
              >
                <div class="flex items-center gap-3">
                  <div class="bg-[#151515] px-3 py-2 rounded text-teal-400 font-mono font-bold text-lg border border-gray-800 shadow-inner">
                     {{ horario.horario_inicio?.substring(0, 5) }}
                  </div>
                  <div>
                     <div class="text-[10px] uppercase text-gray-500 font-bold mb-0.5">{{ horario.duracao_minutos }} min</div>
                     <div class="flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full" :class="horario.ocupacao >= horario.vagas_totais ? 'bg-red-500' : 'bg-green-500'"></span>
                        <span class="text-xs font-medium text-gray-300">
                          {{ horario.ocupacao || 0 }}/{{ horario.vagas_totais }} alunos
                        </span>
                     </div>
                  </div>
                </div>

                <div class="flex gap-2">
                   <button
                      @click="iniciarEdicao(horario)"
                      class="p-2 bg-[#222] hover:bg-blue-900/20 text-blue-400 rounded transition border border-gray-800 hover:border-blue-800"
                      title="Editar">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                   </button>

                   <button
                      v-if="Number(horario.ocupacao) === 0"
                      @click="deletarHorario(horario.id)"
                      class="p-2 bg-[#222] hover:bg-red-900/20 text-red-400 rounded transition border border-gray-800 hover:border-red-800"
                      title="Excluir">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                   </button>

                   <div v-else class="p-2 text-gray-600 cursor-not-allowed opacity-50" title="Ocupado">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                   </div>
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import axios from 'axios';
import { useAlert } from '../../composables/useAlert';

const { mostrarSucesso, mostrarErro, mostrarConfirmacao } = useAlert();

const horarios = ref([]);
const modoEdicao = ref(false);
// Aba ativa começa na Segunda-feira (1)
const abaAtiva = ref(1);

const form = reactive({
  id: null,
  dia_semana: 1,
  horario_inicio: '10:00',
  duracao_minutos: 50,
  vagas_totais: 3,
  status: 'ativo'
});

// Definição das abas para o loop
const diasAbas = [
    { id: 1, nome: 'Segunda' },
    { id: 2, nome: 'Terça' },
    { id: 3, nome: 'Quarta' },
    { id: 4, nome: 'Quinta' },
    { id: 5, nome: 'Sexta' },
    { id: 6, nome: 'Sábado' },
    { id: 7, nome: 'Domingo' }
];

// Computed Property para filtrar os horários pela aba ativa e ordenar por hora
const horariosFiltrados = computed(() => {
    return horarios.value
        .filter(h => parseInt(h.dia_semana) === abaAtiva.value)
        .sort((a, b) => a.horario_inicio.localeCompare(b.horario_inicio));
});

const getNomeDia = (id) => {
    const dia = diasAbas.find(d => d.id === id);
    return dia ? dia.nome : 'Dia Selecionado';
};

const fetchHorarios = async () => {
  try {
    const response = await axios.get('/api/horarios-agenda');
    horarios.value = response.data.data || response.data;
  } catch (error) {
    console.error("Erro ao buscar horários:", error);
    mostrarErro("Erro ao buscar horários. Verifique a conexão com a API.");
  }
};

const iniciarEdicao = (horario) => {
    modoEdicao.value = true;
    form.id = horario.id;
    form.dia_semana = horario.dia_semana;
    form.horario_inicio = horario.horario_inicio;
    form.duracao_minutos = horario.duracao_minutos;
    form.vagas_totais = horario.vagas_totais;
    form.status = horario.status;

    // Opcional: Mudar a aba para o dia que está sendo editado para facilitar visualização
    abaAtiva.value = parseInt(horario.dia_semana);
};

const cancelarEdicao = () => {
    modoEdicao.value = false;
    form.id = null;
    form.dia_semana = abaAtiva.value; // Mantém o dia atual da aba para facilitar cadastro em massa
    form.horario_inicio = '10:00';
    form.duracao_minutos = 50;
    form.vagas_totais = 3;
    form.status = 'ativo';
};

const salvarHorario = async () => {
  if (form.vagas_totais < 1) {
      mostrarErro("O número de vagas deve ser pelo menos 1.");
      return;
  }

  const payload = { ...form, dia_semana: parseInt(form.dia_semana) };

  try {
    if (modoEdicao.value) {
        await axios.put(`/api/horarios-agenda/${form.id}`, payload);
        mostrarSucesso("Horário atualizado com sucesso!");
        modoEdicao.value = false;
    } else {
        await axios.post('/api/horarios-agenda', payload);
        mostrarSucesso("Horário cadastrado com sucesso!");
    }

    cancelarEdicao();
    await fetchHorarios();
    // Garante que a aba mostrada seja a do dia que acabou de ser salvo
    abaAtiva.value = payload.dia_semana;

  } catch (error) {
    if (error.response && error.response.data && error.response.data.message) {
        mostrarErro(error.response.data.message);
    } else {
        mostrarErro("Erro ao salvar horário. Verifique os dados.");
    }
  }
};

const deletarHorario = async (id) => {
    const confirmou = await mostrarConfirmacao('Tem certeza que deseja excluir este horário permanentemente?');

    if (!confirmou) return;

    try {
        await axios.delete(`/api/horarios-agenda/${id}`);
        await fetchHorarios();
        mostrarSucesso("Horário excluído com sucesso.");
    } catch (error) {
        console.error("Erro ao excluir:", error);
        mostrarErro("Não foi possível excluir o horário. Tente novamente.");
    }
};

onMounted(fetchHorarios);
</script>

<style scoped>
.form-input-style {
    @apply w-full bg-[#1e1e1e] border border-[#444] text-white rounded p-2 text-sm focus:border-teal-600 focus:ring-1 focus:ring-teal-600 outline-none;
}
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
