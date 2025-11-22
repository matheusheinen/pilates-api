<template>
  <div class="bg-[#151515] p-6 rounded-xl text-white">
    <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-6 gap-4">
      <div>
        <h3 class="text-lg font-semibold">Agenda de Aulas</h3>
        <p class="text-[#a0a0a0]">Visualize todas as aulas, horários livres e ocupados.</p>
      </div>
      <div>
        <button @click="atualizarAgenda" class="w-full sm:w-auto px-4 py-2 rounded-lg bg-teal-700 hover:bg-teal-600 font-semibold transition-colors text-sm">
          Atualizar Agenda
        </button>
      </div>
    </div>

    <FullCalendar :options="calendarOptions" ref="fullCalendar" />

    <div v-if="showModal" class="modal-overlay" @click="showModal = false">
      <div class="modal-content" @click.stop>
        <h4 class="font-bold text-xl mb-4 text-teal-400">{{ eventTitle }}</h4>

        <div v-if="selectedEventDetails" class="space-y-4">
          <p><strong>Início:</strong> {{ formatarData(selectedEvent.start) }}</p>
          <p><strong>Duração:</strong> {{ selectedEvent.extendedProps.duration_minutes }} minutos</p>
          <p><strong>Vagas:</strong> {{ selectedEventDetails.total_alunos }} / {{ selectedEventDetails.vagas_totais }}</p>

          <h5 class="font-bold text-sm text-gray-400 border-b border-gray-600 pb-1 mt-4">
              Alunos Agendados
          </h5>

          <ul class="list-none text-sm space-y-2 max-h-40 overflow-y-auto pr-2">
            <li v-for="aluno in selectedEventDetails.alunos" :key="aluno.id_aula" class="flex justify-between items-center bg-[#1e1e1e] p-2 rounded-lg">
                <span class="text-white">{{ aluno.nome }}</span>

                <button
                    @click.stop="cancelarAula(aluno.id_aula)"
                    class="text-xs text-red-500 hover:text-red-400 bg-red-900/20 p-1 rounded-md transition duration-150">
                    Cancelar Aula
                </button>
            </li>
          </ul>
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
const selectedEventDetails = ref(null); // Armazena os dados agregados (lista de alunos)
const eventTitle = ref('');
const fullCalendar = ref(null);

// --- 1. FUNÇÃO DE BUSCA DE EVENTOS (AGREGADA) ---
const fetchAulas = async (fetchInfo, successCallback, failureCallback) => {
  try {
    // Chama o novo endpoint que faz a agregação no Backend
    const response = await axios.get('/api/aulas/calendario', {
      params: {
        start: fetchInfo.startStr,
        end: fetchInfo.endStr
      }
    });

    // O Backend já retorna os dados no formato FullCalendar (events)
    successCallback(response.data);

  } catch (error) {
    console.error('Erro ao buscar aulas para o calendário:', error);
    failureCallback(error);
  }
};

// --- 2. CLIQUE NO EVENTO (POPULA MODAL) ---
const handleEventClick = (clickInfo) => {
  selectedEvent.value = clickInfo.event;
  selectedEventDetails.value = clickInfo.event.extendedProps;
  eventTitle.value = clickInfo.event.title;
  showModal.value = true;
};

// --- 3. BOTÃO DE ATUALIZAÇÃO MANUAL ---
const atualizarAgenda = async () => {
  if (!confirm('Deseja gerar/atualizar a agenda para as próximas 60 dias?')) {
    return;
  }

  try {
    // Chama a rota API que executa o Artisan Command (agenda:atualizar)
    const response = await axios.post('/api/agenda/atualizar');

    alert('Agenda atualizada com sucesso! ' + response.data.message);

    // Atualiza a visualização do calendário
    if (fullCalendar.value) {
      fullCalendar.value.getApi().refetchEvents();
    }
  } catch (error) {
    alert(error.response?.data?.message || 'Ocorreu um erro ao atualizar a agenda.');
    console.error('Erro ao atualizar agenda:', error.response?.data || error);
  }
};

// --- 4. FUNÇÃO DE CANCELAMENTO INDIVIDUAL ---
const cancelarAula = async (aulaId) => {
    if (!confirm(`Confirma o cancelamento da aula ID ${aulaId}?`)) return;

    try {
        // Implementar a rota DELETE no Backend (Ex: Route::delete('/api/aulas/{id}', ...))
        // Esta rota deve mudar o status da Aula para 'cancelada' e talvez liberar a vaga para reposição.
        const response = await axios.delete(`/api/aulas/${aulaId}`);

        alert("Aula cancelada com sucesso!");
        showModal.value = false;

        // Refetch de eventos para atualizar a tela
        if (fullCalendar.value) {
            fullCalendar.value.getApi().refetchEvents();
        }
    } catch (error) {
        alert(error.response?.data?.message || "Não foi possível cancelar a aula.");
        console.error('Erro ao cancelar aula:', error);
    }
}


// --- CONFIGURAÇÕES E HELPERS ---
const calendarOptions = ref({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: 'timeGridWeek',
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay'
  },
  timeZone: 'local',
  locale: ptBR,
  buttonText: { today: 'Hoje', month: 'Mês', week: 'Semana', day: 'Dia' },
  allDaySlot: false,
  slotMinTime: "06:00:00",
  slotMaxTime: "22:00:00",
  events: fetchAulas, // Usa o método de agregação
  eventClick: handleEventClick,
  eventDisplay: 'block',
  eventTimeFormat: { hour: '2-digit', minute: '2-digit', meridiem: false } // 10:00
});

const formatarData = (data) => {
  if (!data) return '';
  return format(new Date(data), "dd/MM/yyyy 'às' HH:mm", { locale: ptBR });
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
