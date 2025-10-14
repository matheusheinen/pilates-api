<template>
  <div class="bg-[#151515] p-6 rounded-xl text-white">
    <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-6 gap-4">
      <div>
        <h3 class="text-lg font-semibold">Agenda de Aulas</h3>
        <p class="text-[#a0a0a0]">Visualize todas as aulas, horários livres e ocupados.</p>
      </div>
      <div>
        <button @click="atualizarAgenda" class="w-full sm:w-auto px-4 py-2 rounded-lg bg-teal-700 hover:bg-teal-600 font-semibold transition-colors text-sm">
          Atualizar agenda
        </button>
      </div>
    </div>

    <FullCalendar :options="calendarOptions" ref="fullCalendar" />

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
import { ref } from 'vue';
import axios from 'axios';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import { ptBR } from 'date-fns/locale';
import { format } from 'date-fns';

const showModal = ref(false);
const selectedEvent = ref(null);
const fullCalendar = ref(null);

const handleEventClick = (clickInfo) => {
  selectedEvent.value = clickInfo.event;
  showModal.value = true;
};

const fetchAulas = async (fetchInfo, successCallback, failureCallback) => {
  try {
    const response = await axios.get('/api/aulas', {
      params: { mes: format(fetchInfo.start, 'yyyy-MM') }
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

const atualizarAgenda = async () => {
  if (!confirm('Deseja gerar/atualizar a agenda para as próximas 4 semanas? As aulas já existentes não serão duplicadas.')) {
    return;
  }

  try {
    const response = await axios.post('/api/agenda/gerar-semana');
    alert('Agenda atualizada com sucesso! O calendário será recarregado.');
    console.log('Saída do comando:', response.data.output);

    if (fullCalendar.value) {
      fullCalendar.value.getApi().refetchEvents();
    }

  } catch (error) {
    alert('Ocorreu um erro ao atualizar a agenda.');
    console.error('Erro ao gerar agenda:', error);
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
  // --- CORREÇÃO ADICIONADA AQUI ---
  timeZone: 'local', // Diz ao calendário para usar o fuso horário do navegador
  // --- FIM DA CORREÇÃO ---
  locale: ptBR,
  buttonText: { today: 'Hoje', month: 'Mês', week: 'Semana', day: 'Dia' },
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
