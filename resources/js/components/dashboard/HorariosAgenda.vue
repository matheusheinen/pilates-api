<template>
  <div class="bg-[#151515] p-6 rounded-xl text-white">
    <h3 class="text-lg font-semibold">Gestão de Horários da Agenda</h3>
    <p class="text-[#a0a0a0] mb-6">Defina os horários padrão e quantas vagas cada um possui.</p>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

      <div class="lg:col-span-1 bg-[#242424] p-6 rounded-xl self-start border border-gray-700 shadow-md">
        <form @submit.prevent="adicionarHorario" class="space-y-4">
          <h4 class="font-semibold text-white border-b border-gray-600 pb-2 mb-4">Adicionar Novo Horário</h4>

          <div v-if="mensagemErro" class="p-3 bg-red-900/50 border border-red-800 text-red-200 rounded text-sm">
             {{ mensagemErro }}
          </div>

          <div>
            <label class="block text-xs text-gray-400 mb-1">Dia da Semana</label>
            <select v-model="novoHorario.dia_semana" class="form-input-style" required>
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
            <input v-model="novoHorario.horario_inicio" type="time" class="form-input-style" required>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs text-gray-400 mb-1">Duração (min)</label>
              <input v-model.number="novoHorario.duracao_minutos" type="number" step="5" min="10" class="form-input-style" required>
            </div>

            <div>
              <label class="block text-xs text-gray-400 mb-1">Total de Vagas</label>
              <input v-model.number="novoHorario.vagas_totais" type="number" min="1" class="form-input-style" required>
            </div>
          </div>

          <button type="submit" class="w-full bg-teal-700 hover:bg-teal-600 text-white font-semibold py-2 rounded-lg transition-colors mt-4">
            Cadastrar Horário
          </button>
        </form>
      </div>

      <div class="lg:col-span-2">
        <div class="bg-[#242424] rounded-xl border border-gray-700 overflow-hidden shadow-md">
          <div v-if="!horarios.length" class="p-8 text-center text-gray-500">
              Nenhum horário ativo cadastrado.
          </div>

          <div v-else class="divide-y divide-gray-700">
            <div class="grid grid-cols-10 gap-2 p-3 bg-[#2a2a2a] text-xs text-gray-400 font-medium uppercase tracking-wider">
               <div class="col-span-3">Dia / Hora</div>
               <div class="col-span-2">Duração</div>
               <div class="col-span-3 text-center">Ocupação (Livres)</div>
               <div class="col-span-2 text-right">Ações</div>
            </div>

            <div v-for="horario in horarios" :key="horario.id" class="grid grid-cols-10 items-center gap-2 py-3 px-3 hover:bg-[#2b2b2b] transition duration-150 text-sm">

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
                <p class="text-xs" :class="horario.vagas_totais - horario.ocupacao <= 0 ? 'text-red-500' : 'text-gray-400'">
                    ({{ horario.vagas_totais - (horario.ocupacao || 0) }} Livres)
                </p>
              </div>

              <div class="col-span-2 text-right">
                 <button
                    @click="editarHorario(horario.id)"
                    class="text-blue-400 hover:text-blue-300 hover:bg-blue-900/20 p-2 rounded transition font-semibold text-xs"
                    title="Editar Horário">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    Editar
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
import { useRouter } from 'vue-router'; // Importado para navegação

const router = useRouter(); // Instanciado

const horarios = ref([]);
const mensagemErro = ref('');

const novoHorario = reactive({
  dia_semana: 1,
  horario_inicio: '10:00',
  duracao_minutos: 50,
  vagas_totais: 3,
});

const fetchHorarios = async () => {
  try {
    const response = await axios.get('/api/horarios-agenda');

    // Acessar a chave 'data' da resposta (Assumindo Laravel Resource Collection)
    horarios.value = response.data.data || response.data;

  } catch (error) {
    console.error("Erro ao buscar horários:", error);
    mensagemErro.value = "Erro ao buscar horários. Verifique a conexão com a API.";
  }
};

const adicionarHorario = async () => {
  mensagemErro.value = '';

  if (novoHorario.vagas_totais < 1) {
      mensagemErro.value = "O número de vagas deve ser pelo menos 1.";
      return;
  }
  // Conversão de dia_semana para integer, pois o v-model estava como string '1'
  const payload = { ...novoHorario, dia_semana: parseInt(novoHorario.dia_semana) };

  try {
    await axios.post('/api/horarios-agenda', payload);
    await fetchHorarios();
    alert("Horário cadastrado com sucesso!");
    novoHorario.horario_inicio = '10:00';
  } catch (error) {
    if (error.response && error.response.data && error.response.data.message) {
        mensagemErro.value = error.response.data.message;
    } else {
        mensagemErro.value = "Erro ao adicionar horário. Verifique os dados.";
    }
  }
};

// --- NOVA FUNÇÃO: EDICÃO ---
const editarHorario = (id) => {
    // Implemente a navegação para a tela/modal de edição.
    // Exemplo de navegação para uma rota chamada 'EditarHorario':
    router.push({ name: 'EditarHorario', params: { id: id } });

    // Se a edição for um modal, a lógica seria diferente (mostrarModal(id)).
};

// Funções de Utilitário
const formatarDiaSemana = (dia) => {
  const dias = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
  // Ajuste para Carbon ISO Week Day (1=Segunda, 7=Domingo)
  return dias[dia === 7 ? 0 : dia];
};

onMounted(fetchHorarios);
</script>

<style scoped>
/* Estilos aplicados para o novo layout */
.form-input-style {
    @apply w-full bg-[#1e1e1e] border border-[#444] text-white rounded p-2 text-sm focus:border-teal-600 focus:ring-1 focus:ring-teal-600 outline-none;
}
.form-select-style {
    @apply w-full bg-[#1e1e1e] border border-[#444] text-white rounded p-2 text-sm focus:border-teal-600 focus:ring-1 focus:ring-teal-600 outline-none;
}
</style>
