<template>
  <div class="bg-[#151515] p-6 rounded-xl text-white">
    <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-6 gap-4">
      <div>
        <h3 class="text-lg font-semibold">Agenda de Aulas</h3>
        <p class="text-[#a0a0a0]">Visualize todas as aulas, horários livres e ocupados.</p>
      </div>
      <div>
        <button
            @click="atualizarAgenda"
            :disabled="atualizando"
            class="w-full sm:w-auto px-4 py-2 rounded-lg bg-teal-700 hover:bg-teal-600 font-semibold transition-colors text-sm flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
          <svg v-if="atualizando" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          <span>{{ atualizando ? 'Atualizando...' : 'Atualizar Agenda' }}</span>
        </button>
      </div>
    </div>

    <FullCalendar :options="calendarOptions" ref="fullCalendar" />

    <div v-if="showModal" class="modal-overlay" @click="showModal = false">
      <div class="modal-content" @click.stop>
        <h4 class="font-bold text-xl mb-4 text-teal-400">{{ eventTitle }}</h4>

        <div v-if="selectedEventDetails" class="space-y-4">
          <p><strong>Início:</strong> {{ formatarData(selectedEvent.start) }}</p>
          <p><strong>Duração:</strong> 50 minutos</p>
          <p><strong>Vagas:</strong> {{ selectedEventDetails.total_alunos }} / {{ selectedEventDetails.vagas_totais }}</p>

          <h5 class="font-semibold text-lg mt-4 border-b border-gray-600 pb-2">Alunos Matriculados</h5>

          <ul v-if="selectedEventDetails.alunos && selectedEventDetails.alunos.length > 0" class="space-y-2 max-h-40 overflow-y-auto">
            <li v-for="aluno in selectedEventDetails.alunos" :key="aluno.id_inscricao" class="bg-[#2a2a2a] p-2 rounded flex justify-between items-center text-sm">
                <span>{{ aluno.nome }}</span>
                <span class="text-xs px-2 py-1 rounded bg-gray-700 uppercase">{{ aluno.status }}</span>
            </li>
          </ul>
          <p v-else class="text-gray-500 italic text-sm">Nenhum aluno nesta aula.</p>
        </div>

        <div class="mt-6 text-right">
          <button @click="showModal = false" class="px-4 py-2 bg-gray-600 hover:bg-gray-500 rounded text-white text-sm">Fechar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import axios from 'axios';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
// CORREÇÃO 1: Renomear import do FullCalendar para evitar conflito
import ptBrFullCalendar from '@fullcalendar/core/locales/pt-br';
import { format } from 'date-fns';
// CORREÇÃO 2: Importar locale correto do date-fns
import { ptBR } from 'date-fns/locale';

const fullCalendar = ref(null);
const showModal = ref(false);
const selectedEvent = ref(null);
const selectedEventDetails = ref(null);
const eventTitle = ref('');
const atualizando = ref(false);

const fetchAulas = async (info, successCallback, failureCallback) => {
  try {
    const response = await axios.get('/api/aulas/calendario', {
      params: {
        start: info.startStr,
        end: info.endStr
      }
    });
    successCallback(response.data);
  } catch (error) {
    console.error("Erro ao buscar aulas:", error);
    failureCallback(error);
  }
};

const handleEventClick = (info) => {
  selectedEvent.value = info.event;
  selectedEventDetails.value = info.event.extendedProps;
  eventTitle.value = info.event.title;
  showModal.value = true;
};

const atualizarAgenda = async () => {
  if (!confirm('Deseja atualizar a agenda? Isso irá marcar as aulas passadas como "Realizadas".')) {
      return;
  }

  atualizando.value = true;

  try {
    const response = await axios.post('/api/agenda/atualizar');
    alert(response.data.message || 'Agenda atualizada com sucesso!');

    if (fullCalendar.value) {
        fullCalendar.value.getApi().refetchEvents();
    }

  } catch (error) {
    console.error("Erro ao atualizar agenda:", error);
    let mensagemErro = 'Não foi possível atualizar a agenda.';
    if (error.response && error.response.data && error.response.data.message) {
        mensagemErro = error.response.data.message;
    }
    alert(mensagemErro);
  } finally {
    atualizando.value = false;
  }
};

const calendarOptions = reactive({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: 'timeGridWeek',
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay'
  },
  timeZone: 'local',
  // CORREÇÃO 3: Usar o locale renomeado do FullCalendar
  locale: ptBrFullCalendar,
  buttonText: { today: 'Hoje', month: 'Mês', week: 'Semana', day: 'Dia' },
  allDaySlot: false,
  slotMinTime: "06:00:00",
  slotMaxTime: "22:00:00",
  events: fetchAulas,
  eventClick: handleEventClick,
  eventDisplay: 'block',
  eventTimeFormat: { hour: '2-digit', minute: '2-digit', meridiem: false }
});

const formatarData = (data) => {
  if (!data) return '';
  // CORREÇÃO 4: Aqui o date-fns usa o locale correto importado dele
  return format(new Date(data), "dd/MM/yyyy 'às' HH:mm", { locale: ptBR });
};
</script>

<style scoped>
.modal-overlay {
  @apply fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4 backdrop-blur-sm;
}
.modal-content {
  @apply bg-[#1f1f1f] rounded-xl border border-gray-600 w-full max-w-md p-6 shadow-2xl;
}
:deep(.fc) {
  --fc-border-color: #374151;
  --fc-daygrid-event-dot-width: 8px;
  --fc-list-event-dot-width: 10px;
  --fc-event-text-color: #ffffff;
  --fc-page-bg-color: #151515;
  --fc-neutral-bg-color: #1a1a1a;
  --fc-list-event-hover-bg-color: #2a2a2a;
  --fc-today-bg-color: rgba(20, 184, 166, 0.1);
}
:deep(.fc-col-header-cell), :deep(.fc-timegrid-slot-label) {
    @apply text-gray-400 font-normal;
}
:deep(.fc-timegrid-slot) {
    @apply hover:bg-white/5 transition-colors;
}
</style>
