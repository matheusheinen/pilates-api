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
            class="w-full sm:w-auto px-4 py-2 rounded-lg bg-teal-700 hover:bg-teal-600 font-semibold transition-colors text-sm flex items-center justify-center gap-2 disabled:opacity-50">
          <svg v-if="atualizando" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
          <span>{{ atualizando ? 'Processando...' : 'Concluir Aulas Passadas' }}</span>
        </button>
      </div>
    </div>

    <FullCalendar :options="calendarOptions" ref="fullCalendar" />

    <div v-if="showModal" class="modal-overlay" @click="showModal = false">
      <div class="modal-content max-w-md p-6" @click.stop>
        <div class="flex justify-between items-start mb-4">
            <h4 class="font-bold text-xl text-teal-400">{{ eventTitle }}</h4>
            <button @click="showModal = false" class="text-gray-400 hover:text-white text-xl font-bold">×</button>
        </div>

        <div v-if="selectedEventDetails" class="space-y-4">
          <div class="flex justify-between text-sm text-gray-300 bg-[#2a2a2a] p-3 rounded-lg border border-gray-700">
              <span><strong>Início:</strong> {{ formatarData(selectedEvent.start) }}</span>
              <span><strong>Vagas:</strong> {{ selectedEventDetails.total_alunos }} / {{ selectedEventDetails.vagas_totais }}</span>
          </div>

          <div>
              <h5 class="font-semibold text-xs text-gray-400 uppercase tracking-wider mb-2">Alunos Matriculados</h5>
              <ul v-if="selectedEventDetails.alunos && selectedEventDetails.alunos.length > 0" class="space-y-2 max-h-60 overflow-y-auto pr-1 scrollbar-thin">
                <li v-for="aluno in selectedEventDetails.alunos" :key="aluno.id_inscricao" class="bg-[#2a2a2a] p-3 rounded-lg flex justify-between items-center text-sm group border border-transparent hover:border-gray-600 transition-colors">
                    <div class="flex flex-col">
                        <span class="font-medium text-white">{{ aluno.nome }}</span>
                        <span class="text-[10px] uppercase font-bold" :class="statusClass(aluno.status)">{{ aluno.status }}</span>
                    </div>
                    <div v-if="aluno.status === 'agendada'" class="flex gap-2">
                        <button @click="abrirModalReagendar(aluno)" class="p-1.5 bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 rounded transition border border-blue-500/20" title="Reagendar Aluno">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 21h5v-5"/></svg>
                        </button>
                        <button @click="cancelarAula(aluno)" class="p-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-400 rounded transition border border-red-500/20" title="Cancelar Aluno">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>
                </li>
              </ul>
              <p v-else class="text-gray-500 italic text-sm py-4 text-center">Nenhum aluno nesta aula.</p>
          </div>
        </div>

        <div v-if="temAlunosAtivos" class="mt-6 pt-4 border-t border-gray-700 flex gap-3">
            <button @click="cancelarEventoEmMassa" class="flex-1 py-2.5 px-3 border border-red-500/30 text-red-400 hover:bg-red-500/10 rounded-lg text-xs font-bold uppercase transition">
                Cancelar Turma
            </button>
            <button @click="abrirModalReagendarEmMassa" class="flex-1 py-2.5 px-3 bg-teal-700 hover:bg-teal-600 text-white rounded-lg text-xs font-bold uppercase transition shadow-lg shadow-teal-900/20">
                Reagendar Turma
            </button>
        </div>
      </div>
    </div>

    <transition name="fade">
    <div v-if="showReagendar" class="modal-overlay" @click="fecharReagendar">

      <div class="modal-content w-full max-w-4xl flex flex-col max-h-[90vh] overflow-hidden bg-[#1f1f1f] border border-gray-700 shadow-2xl rounded-xl" @click.stop>

        <div class="px-6 py-4 border-b border-gray-700 flex justify-between items-center bg-[#252525] rounded-t-xl">
            <div>
                <h3 class="text-white font-bold text-xl">
                    {{ isBulkReagendamento ? 'Reagendar Turma Completa' : 'Reagendar Aluno' }}
                </h3>
                <p class="text-sm text-gray-400 mt-0.5">
                    <span v-if="isBulkReagendamento">Movendo <strong>{{ selectedEventDetails.total_alunos }} alunos</strong>.</span>
                    <span v-else>Aluno: <span class="text-teal-400 font-semibold">{{ alunoParaReagendar?.nome }}</span></span>
                </p>
            </div>
            <button @click="fecharReagendar" class="bg-gray-700 hover:bg-gray-600 text-gray-300 hover:text-white p-2 rounded-full transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        <div class="bg-[#1a1a1a] px-4 py-3 flex justify-between items-center border-b border-gray-800 shadow-inner">
            <button @click="mudarSemanaReagendamento(-1)" class="p-2 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition bg-[#252525] border border-gray-700" title="Semana Anterior">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            </button>

            <div class="text-center">
                <span class="block text-[10px] text-gray-500 uppercase font-bold tracking-wider mb-0.5">Semana de</span>
                <span class="text-sm font-semibold text-white">{{ formatarIntervaloSemana(semanaReagendamento) }}</span>
            </div>

            <button @click="mudarSemanaReagendamento(1)" class="p-2 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition bg-[#252525] border border-gray-700" title="Próxima Semana">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6 bg-[#1f1f1f] min-h-[400px]">

            <div v-if="loadingVagas" class="flex flex-col items-center justify-center py-20 text-gray-500">
                <svg class="animate-spin h-10 w-10 mb-4 text-teal-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <span class="text-base font-medium">Buscando horários...</span>
            </div>

            <div v-else-if="vagasDisponiveis.length === 0" class="text-center py-20 px-6 border-2 border-dashed border-gray-700 rounded-xl mx-4">
                <p class="text-gray-300 font-medium text-lg">Sem vagas nesta semana.</p>
                <p class="text-sm text-gray-500 mt-2 leading-relaxed">
                    Não há horários livres compatíveis na semana selecionada. Tente navegar para a próxima semana.
                </p>
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <button
                    v-for="vaga in vagasDisponiveis"
                    :key="vaga.data_hora"
                    @click="confirmarReagendamento(vaga)"
                    :disabled="isBulkReagendamento && vaga.vagas_restantes < selectedEventDetails.total_alunos"
                    class="relative bg-[#2a2a2a] hover:bg-teal-900/20 border border-gray-700 p-4 rounded-xl flex flex-col gap-3 group transition-all hover:border-teal-500/50 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:border-gray-700 disabled:hover:bg-[#2a2a2a] shadow-sm hover:shadow-md"
                >
                    <div class="flex items-center justify-between w-full">
                        <div class="flex items-center gap-4">
                            <div class="bg-[#333] w-12 h-12 rounded-lg flex flex-col items-center justify-center border border-gray-600 group-hover:border-teal-500/30 transition-colors shadow-inner">
                                <span class="text-[9px] uppercase text-gray-400 font-bold">{{ vaga.dia_semana.substring(0,3) }}</span>
                                <span class="text-lg font-bold text-white leading-none">{{ formatarDiaNumero(vaga.data_hora) }}</span>
                            </div>

                            <div>
                                <div class="flex items-baseline gap-2">
                                    <p class="text-teal-400 font-bold text-2xl leading-none tracking-tight">{{ vaga.horario.substring(0, 5) }}</p>

                                    <span class="text-[10px] text-gray-500 font-bold bg-[#151515] px-1.5 py-0.5 rounded border border-gray-700">
                                        {{ vaga.duracao || 50 }} min
                                    </span>
                                </div>
                                <p class="text-xs text-gray-400 mt-0.5 capitalize">{{ vaga.dia_semana }} - {{ formatarMes(vaga.data_hora) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="w-full flex items-center justify-between border-t border-gray-700 pt-3 mt-1">
                        <span class="text-[11px] text-gray-500 font-medium flex items-center gap-1">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span> Disponível
                        </span>
                        <span class="text-xs px-2.5 py-1 rounded-md font-bold border shadow-sm"
                            :class="isBulkReagendamento && vaga.vagas_restantes < selectedEventDetails.total_alunos
                                ? 'bg-red-900/20 text-red-400 border-red-500/30'
                                : 'bg-gray-800 text-green-400 border-gray-600 group-hover:bg-teal-900/40 group-hover:text-teal-200 group-hover:border-teal-500/30'">
                            {{ vaga.vagas_restantes }} vagas
                        </span>
                    </div>

                    <div v-if="isBulkReagendamento && vaga.vagas_restantes < selectedEventDetails.total_alunos" class="absolute inset-0 bg-black/60 backdrop-blur-[1px] flex items-center justify-center rounded-xl z-10">
                        <span class="bg-red-900 text-red-100 text-[10px] px-2 py-1 rounded font-bold shadow-lg border border-red-500">Insuficiente</span>
                    </div>
                </button>
            </div>
        </div>
      </div>
    </div>
    </transition>

  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import axios from 'axios';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import ptBrFullCalendar from '@fullcalendar/core/locales/pt-br';
import { format, parseISO, addWeeks, subWeeks, startOfWeek, endOfWeek } from 'date-fns';
import { ptBR } from 'date-fns/locale';

// Estados
const fullCalendar = ref(null);
const showModal = ref(false);
const selectedEvent = ref(null);
const selectedEventDetails = ref(null);
const eventTitle = ref('');
const atualizando = ref(false);
const showReagendar = ref(false);
const alunoParaReagendar = ref(null);
const isBulkReagendamento = ref(false);
const vagasDisponiveis = ref([]);
const loadingVagas = ref(false);
const semanaReagendamento = ref(new Date());
const salvandoReagendamento = ref(false);
const formReagendar = reactive({ data: '', id_agenda: '' });

const temAlunosAtivos = computed(() => {
    if (!selectedEventDetails.value?.alunos) return false;
    const temAgendada = selectedEventDetails.value.alunos.some(a => a.status === 'agendada');
    const ehFuturo = new Date(selectedEvent.value.start) > new Date();
    return temAgendada && ehFuturo;
});

const fetchAulas = async (info, successCallback, failureCallback) => {
  try {
    const response = await axios.get('/api/aulas/calendario', {
      params: { start: info.startStr, end: info.endStr }
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
  if (!confirm('Deseja limpar aulas passadas?')) return;
  atualizando.value = true;
  try {
    await axios.post('/api/agenda/atualizar');
    alert('Agenda atualizada.');
    fullCalendar.value.getApi().refetchEvents();
  } catch(e) { alert('Erro ao atualizar.'); }
  finally { atualizando.value = false; }
};

const statusClass = (status) => {
    const map = { 'agendada': 'text-blue-400', 'realizada': 'text-green-400', 'cancelada': 'text-red-400', 'reagendada': 'text-yellow-400' };
    return map[status] || 'text-gray-400';
};

const cancelarAula = async (aluno) => {
    if (!confirm(`Cancelar a aula de ${aluno.nome}?`)) return;
    try {
        await axios.post(`/api/aulas/${aluno.id_aula}/cancelar`);
        alert('Cancelado!');
        showModal.value = false;
        fullCalendar.value.getApi().refetchEvents();
    } catch(e) { alert('Erro ao cancelar.'); }
};

const cancelarEventoEmMassa = async () => {
    if (!confirm('ATENÇÃO: Cancelar TODA a turma?')) return;
    try {
        await axios.post('/api/aulas/cancelar-turma', {
            data: selectedEventDetails.value.data_base,
            horario_agenda_id: selectedEventDetails.value.horario_agenda_id
        });
        alert('Turma cancelada!');
        showModal.value = false;
        fullCalendar.value.getApi().refetchEvents();
    } catch(e) { alert('Erro ao cancelar turma.'); }
};

const abrirModalReagendar = (aluno) => {
    isBulkReagendamento.value = false;
    alunoParaReagendar.value = aluno;
    semanaReagendamento.value = new Date(selectedEvent.value.start);
    showReagendar.value = true;
    buscarVagas();
};

const abrirModalReagendarEmMassa = () => {
    isBulkReagendamento.value = true;
    alunoParaReagendar.value = null;
    semanaReagendamento.value = new Date(selectedEvent.value.start);
    showReagendar.value = true;
    buscarVagas();
};

const fecharReagendar = () => {
    showReagendar.value = false;
    alunoParaReagendar.value = null;
    vagasDisponiveis.value = [];
};

const mudarSemanaReagendamento = (direcao) => {
    if (direcao === 1) semanaReagendamento.value = addWeeks(semanaReagendamento.value, 1);
    else semanaReagendamento.value = subWeeks(semanaReagendamento.value, 1);
    buscarVagas();
};

const buscarVagas = async () => {
    loadingVagas.value = true;
    try {
        const dataBase = format(semanaReagendamento.value, 'yyyy-MM-dd');
        let url = `/api/aulas/disponiveis-reagendamento?data_base=${dataBase}`;
        if (!isBulkReagendamento.value && alunoParaReagendar.value) {
            url += `&aula_id=${alunoParaReagendar.value.id_aula}`;
        }
        const response = await axios.get(url);
        vagasDisponiveis.value = response.data;

        if (isBulkReagendamento.value && dataBase === selectedEventDetails.value.data_base) {
            vagasDisponiveis.value = vagasDisponiveis.value.filter(h => h.id_agenda !== selectedEventDetails.value.horario_agenda_id);
        }
    } catch (error) { console.error(error); }
    finally { loadingVagas.value = false; }
};

const confirmarReagendamento = async (vaga) => {
    const msg = isBulkReagendamento.value
        ? `Mover TODA a turma para ${vaga.dia_semana} às ${vaga.horario.substring(0,5)}?`
        : `Mover ${alunoParaReagendar.value.nome} para ${vaga.dia_semana} às ${vaga.horario.substring(0,5)}?`;

    if (!confirm(msg)) return;

    salvandoReagendamento.value = true;
    try {
        if (isBulkReagendamento.value) {
            await axios.post('/api/aulas/reagendar-turma', {
                data_origem: selectedEventDetails.value.data_base,
                id_agenda_origem: selectedEventDetails.value.horario_agenda_id,
                nova_data: vaga.data_hora,
                id_agenda_destino: vaga.id_agenda
            });
            alert('Turma reagendada!');
        } else {
            const novaDataHora = `${vaga.data_hora.split('T')[0]} ${vaga.horario}`;
            await axios.post(`/api/aulas/${alunoParaReagendar.value.id_aula}/reagendar`, {
                nova_data_hora: novaDataHora,
                id_agenda_destino: vaga.id_agenda
            });
            alert('Aluno reagendado!');
        }
        fecharReagendar();
        showModal.value = false;
        fullCalendar.value.getApi().refetchEvents();
    } catch (error) {
        alert('Erro: ' + (error.response?.data?.message || 'Erro desconhecido'));
    } finally {
        salvandoReagendamento.value = false;
    }
};

const formatarData = (data) => data ? format(new Date(data), "dd/MM/yyyy 'às' HH:mm", { locale: ptBR }) : '';
const formatarDiaNumero = (iso) => format(parseISO(iso), 'dd');
const formatarMes = (iso) => format(parseISO(iso), 'MMM', { locale: ptBR });
const formatarIntervaloSemana = (dataRef) => {
    const inicio = startOfWeek(new Date(dataRef), { weekStartsOn: 0 });
    const fim = endOfWeek(new Date(dataRef), { weekStartsOn: 0 });
    return `${format(inicio, 'dd/MM')} a ${format(fim, 'dd/MM')}`;
};

const calendarOptions = reactive({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: 'timeGridWeek',
  headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' },
  timeZone: 'local',
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
</script>

<style scoped>
.modal-overlay {
  @apply fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4 backdrop-blur-sm;
}
/* Largura flexível controlada pelo template */
.modal-content {
  @apply bg-[#1f1f1f] rounded-xl border border-gray-600 shadow-2xl;
}
.scrollbar-thin::-webkit-scrollbar { width: 6px; }
.scrollbar-thin::-webkit-scrollbar-track { background: #2a2a2a; }
.scrollbar-thin::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 10px; }
:deep(.fc) {
  --fc-border-color: #374151;
  --fc-event-text-color: #ffffff;
  --fc-page-bg-color: #151515;
  --fc-neutral-bg-color: #1a1a1a;
  --fc-today-bg-color: rgba(20, 184, 166, 0.1);
}
:deep(.fc-timegrid-slot) { @apply hover:bg-white/5 transition-colors; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
