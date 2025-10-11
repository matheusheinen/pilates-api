<template>
  <div class="bg-[#151515] p-6 rounded-xl text-white">
    <h3 class="text-lg font-semibold">Agenda de Aulas</h3>
    <p class="text-[#a0a0a0] mb-6">Visualize todas as aulas, horários livres e ocupados.</p>

    <FullCalendar :options="calendarOptions" />

    <div v-if="showModal" class="modal-overlay" @click="showModal = false">
      <div class="modal-content" @click.stop>
        <h4 class="font-bold text-xl mb-4">Detalhes da Aula</h4>
        <div v-if="selectedEvent">
          <p><strong>Início:</strong> {{ formatarData(selectedEvent.start) }}</p>
          <p><strong>Fim:</strong> {{ formatarData(selectedEvent.end) }}</p>
          <p><strong>Status:</strong> <span :class="statusClass(selectedEvent.extendedProps.status)">{{ selectedEvent.extendedProps.status }}</span></p>
          <p v-if="selectedEvent.extendedProps.aluno"><strong>Aluno:</strong> {{ selectedEvent.extendedProps.aluno }}</p>
        </div>
        <button @click="showModal = false" class="mt-6 w-full py-2 rounded-lg bg-gray-600 hover:bg-gray-500 font-semibold">Fechar</button>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
// CORREÇÃO: O caminho da importação foi ajustado para ser mais específico.
import { ptBR } from 'date-fns/locale';
import { format } from 'date-fns';

const showModal = ref(false);
const selectedEvent = ref(null);

const handleEventClick = (clickInfo) => {
  selectedEvent.value = clickInfo.event;
  showModal.value = true;
};

const fetchAulas = async (fetchInfo, successCallback, failureCallback) => {
  try {
    const response = await axios.get('/api/aulas', {
      params: {
        mes: format(fetchInfo.start, 'yyyy-MM')
      }
    });

    const events = response.data.map(aula => ({
      id: aula.id,
      title: aula.inscricao?.usuario ? aula.inscricao.usuario.nome : 'Disponível',
      start: aula.data_hora_inicio,
      end: new Date(new Date(aula.data_hora_inicio).getTime() + aula.duracao_minutos * 60000),
      color: aula.inscricao ? '#009088' : '#4a5568',
      extendedProps: {
        status: aula.status,
        aluno: aula.inscricao?.usuario?.nome || null,
      }
    }));

    successCallback(events);
  } catch (error) {
    console.error('Erro ao buscar aulas:', error);
    failureCallback(error);
  }
};

const calendarOptions = ref({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: 'timeGridWeek',
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay'
  },
  locale: ptBR, // Usamos o objeto importado aqui
  buttonText: {
    today: 'Hoje',
    month: 'Mês',
    week: 'Semana',
    day: 'Dia'
  },
  allDaySlot: false,
  slotMinTime: "06:00:00",
  slotMaxTime: "22:00:00",
  events: fetchAulas,
  eventClick: handleEventClick,
});

const formatarData = (data) => {
  if (!data) return '';
  return format(new Date(data), "dd/MM/yyyy 'às' HH:mm", { locale: ptBR });
};
const statusClass = (status) => {
  if (status === 'agendada') return 'text-green-400';
  if (status === 'disponivel') return 'text-yellow-400';
  return 'text-gray-400';
};
</script>

<style>
/* Estilos permanecem os mesmos */
:root {
  --fc-border-color: #374151;
  --fc-daygrid-event-dot-width: 8px;
  --fc-list-event-dot-width: 10px;
  --fc-event-text-color: #ffffff;
}
.fc .fc-toolbar-title { font-size: 1.25rem; }
.fc .fc-button-primary { background-color: #374151; border-color: #374151; }
.fc .fc-button-primary:hover { background-color: #4b5563; }
.fc-theme-standard .fc-list-day-cushion { background-color: #1f2937; }

.modal-overlay {
  position: fixed; top: 0; left: 0; width: 100%; height: 100%;
  background-color: rgba(0, 0, 0, 0.7);
  display: flex; justify-content: center; align-items: center; z-index: 100;
}
.modal-content {
  background-color: #1f2937; padding: 2rem; border-radius: 0.75rem;
  width: 90%; max-width: 500px;
}
</style>
