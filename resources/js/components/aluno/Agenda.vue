<template>
  <div class="space-y-6 pb-24">

    <div class="flex justify-between items-center px-2 pt-2">
      <div>
        <h2 class="text-xl font-bold text-white leading-tight">Agenda Completa</h2>
        <p class="text-xs text-gray-500">Gerencie suas aulas</p>
      </div>
      <button @click="atualizar" :disabled="loading" class="p-2 text-gray-400 hover:text-white rounded-full hover:bg-white/10 transition active:scale-95">
        <svg :class="{'animate-spin': loading}" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9c5.2 0 9 3.2 9 8"/></svg>
      </button>
    </div>

    <div class="bg-[#1e1e1e] rounded-2xl p-4 border border-gray-800 shadow-lg">

        <div class="flex justify-between items-center mb-4 px-1">
            <button @click="semanaAnterior" class="p-2 text-gray-400 hover:text-white hover:bg-white/5 rounded-full transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            </button>

            <h3 class="text-white font-bold text-lg capitalize tracking-wide">
                {{ mesAnoFormatado }}
            </h3>

            <button @click="proximaSemana" class="p-2 text-gray-400 hover:text-white hover:bg-white/5 rounded-full transition">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </button>
        </div>

        <div class="grid grid-cols-7 gap-1 text-center">
            <div
                v-for="dia in diasSemana"
                :key="dia.iso"
                @click="selecionarDia(dia.iso)"
                class="flex flex-col items-center justify-center py-2 rounded-xl cursor-pointer transition-all relative"
                :class="dia.iso === dataSelecionada
                    ? 'bg-teal-600 text-white shadow-md shadow-teal-900/50 scale-105 z-10'
                    : 'text-gray-400 hover:bg-white/5'"
            >
                <span class="text-[10px] font-bold uppercase mb-1 opacity-70">{{ dia.letra }}</span>
                <span class="text-base font-bold">{{ dia.numero }}</span>

                <div class="h-1.5 w-1.5 mt-1 rounded-full"
                     :class="{
                        'bg-white': dia.iso === dataSelecionada && temAulaNoDia(dia.iso),
                        'bg-teal-500': dia.iso !== dataSelecionada && temAulaNoDia(dia.iso),
                        'bg-transparent': !temAulaNoDia(dia.iso)
                     }">
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-4 min-h-[300px]">

      <div v-if="loading" class="py-10 text-center text-gray-500 animate-pulse text-sm">
        Buscando sua agenda...
      </div>

      <div v-else-if="aulasDoDia.length === 0" class="flex flex-col items-center justify-center py-12 text-center bg-[#1e1e1e] rounded-2xl border border-gray-800/50 border-dashed mx-1">
        <div class="bg-gray-800/50 p-3 rounded-full mb-3 text-gray-600">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <p class="text-gray-400 text-sm font-medium">Dia Livre</p>
        <p class="text-xs text-gray-600 mt-0.5">Sem aulas para {{ formatarDataSimples(dataSelecionada) }}.</p>
      </div>

      <div v-else class="space-y-3 px-1">
        <div
            v-for="aula in aulasDoDia"
            :key="aula.id"
            class="relative overflow-hidden bg-[#1e1e1e] rounded-xl border border-gray-800 shadow-sm active:scale-[0.99] transition-all"
        >
            <div class="absolute left-0 top-0 bottom-0 w-1" :class="statusColor(aula.extendedProps?.status)"></div>

            <div class="p-4 flex items-center justify-between">
                <div class="flex items-center gap-4 pl-2">
                    <div class="flex flex-col items-center min-w-[2.5rem]">
                        <span class="text-lg font-bold text-white tracking-tight leading-none">{{ formatarHora(aula.start) }}</span>
                    </div>
                    <div class="w-px h-8 bg-gray-700/50"></div>
                    <div>
                        <h3 class="text-gray-200 font-semibold text-sm leading-tight">Pilates</h3>
                        <p class="text-[10px] text-gray-500 mt-0.5">50 min • Presencial</p>

                        <p v-if="aula.extendedProps?.status === 'reagendada' && aula.extendedProps?.observacoes"
                        class="text-[10px] text-yellow-500 font-medium mt-1 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 10 20 15 15 20"></polyline><path d="M4 4v7a4 4 0 0 0 4 4h12"></path></svg>
                            {{ aula.extendedProps.observacoes }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-col items-end gap-2">
                    <span :class="statusBadge(aula.extendedProps?.status)" class="text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wide">
                        {{ aula.extendedProps?.status || '---' }}
                    </span>

                    <button
                        v-if="podeReagendar(aula)"
                        @click="abrirReagendamento(aula)"
                        class="text-[10px] font-semibold text-teal-500 bg-teal-500/10 hover:bg-teal-500/20 px-2 py-1 rounded transition border border-teal-500/20"
                    >
                        Reagendar
                    </button>
                </div>
            </div>
        </div>
      </div>
    </div>

    <transition name="slide-up">
    <div v-if="modalReagendar" class="fixed inset-0 z-[60] flex flex-col justify-end">

        <div class="absolute inset-0 bg-black/80 backdrop-blur-sm transition-opacity" @click="fecharReagendamento"></div>

        <div class="relative bg-[#181818] w-full rounded-t-2xl border-t border-gray-700 shadow-2xl flex flex-col max-h-[80vh] overflow-hidden safe-area-pb">

            <div class="w-full flex justify-center pt-3 pb-1" @click="fecharReagendamento">
                <div class="w-12 h-1.5 bg-gray-700 rounded-full"></div>
            </div>

            <div class="px-5 pb-4 border-b border-gray-800 flex justify-between items-end">
                <div>
                    <h3 class="text-white font-bold text-lg">Reagendar Aula</h3>
                    <p class="text-xs text-gray-400 mt-1">Original: <span class="text-teal-400 font-medium">{{ formatarDataHora(aulaParaReagendar?.start) }}</span></p>
                </div>
                <button @click="fecharReagendamento" class="bg-gray-800 text-gray-400 hover:text-white p-1.5 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </div>

            <div class="bg-[#131313] px-4 py-3 flex justify-center items-center border-b border-gray-800 text-center">
                <p class="text-xs text-gray-400">
                    Exibindo horários disponíveis para a semana de <br>
                    <strong class="text-white text-sm uppercase">{{ formatarIntervaloSemana(semanaReagendamento) }}</strong>
                </p>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-2 bg-[#181818]">

                <div v-if="loadingVagas" class="flex flex-col items-center justify-center py-10 text-gray-500">
                    <svg class="animate-spin h-6 w-6 mb-2 text-teal-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span class="text-xs">Buscando horários...</span>
                </div>

                <div v-else-if="vagasDisponiveis.length === 0" class="text-center py-10 px-4">
                    <p class="text-gray-300 text-sm font-medium mb-1">Sem vagas nesta semana.</p>
                    <p class="text-xs text-gray-500">
                        Não há horários disponíveis para reagendamento na semana da sua aula original.
                    </p>
                </div>

                <button
                    v-for="vaga in vagasDisponiveis"
                    :key="vaga.data_hora"
                    @click="confirmarReagendamento(vaga)"
                    class="w-full bg-[#232323] active:bg-[#2a2a2a] border border-gray-700 p-3 rounded-xl flex justify-between items-center group transition-all hover:border-teal-500/50"
                >
                    <div class="text-left flex items-center gap-3">
                        <div class="bg-[#2f2f2f] w-10 h-10 rounded-lg flex flex-col items-center justify-center border border-gray-600">
                            <span class="text-[8px] uppercase text-gray-400 font-bold">{{ vaga.dia_semana.substring(0,3) }}</span>
                            <span class="text-sm font-bold text-white leading-none">{{ formatarDiaNumero(vaga.data_hora) }}</span>
                        </div>

                        <div>
                            <p class="text-teal-400 font-bold text-lg leading-none">{{ vaga.horario.substring(0, 5) }}</p>
                            <p class="text-[10px] text-gray-500 mt-0.5 capitalize">{{ vaga.dia_semana }}</p>
                        </div>
                    </div>

                    <div class="text-right">
                        <span class="text-[10px] bg-gray-800 text-gray-300 px-2 py-1 rounded border border-gray-700">
                            {{ vaga.vagas_restantes }} vaga(s)
                        </span>
                    </div>
                </button>
            </div>
        </div>
    </div>
    </transition>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { format, parseISO, addDays, startOfWeek, endOfWeek, addWeeks, subWeeks, isSameDay } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { useAlert } from '../../composables/useAlert';

const { mostrarSucesso, mostrarErro, mostrarConfirmacao } = useAlert();


const loading = ref(false);
const aulas = ref([]);

// Data de referência para a navegação do calendário (semana atual)
const dataReferencia = ref(new Date());
// Data selecionada para filtrar a lista de aulas
const dataSelecionada = ref(format(new Date(), 'yyyy-MM-dd'));

// Reagendamento
const modalReagendar = ref(false);
const aulaParaReagendar = ref(null);
const vagasDisponiveis = ref([]);
const loadingVagas = ref(false);
const semanaReagendamento = ref(new Date());

// --- CALENDÁRIO HOME (7 Dias) ---
const diasSemana = computed(() => {
    // Começa no Domingo (0) ou Segunda (1) conforme preferência. Aqui: Domingo.
    const inicioSemana = startOfWeek(dataReferencia.value, { weekStartsOn: 0 });
    const dias = [];
    for (let i = 0; i < 7; i++) {
        const data = addDays(inicioSemana, i);
        dias.push({
            iso: format(data, 'yyyy-MM-dd'),
            numero: format(data, 'dd'),
            letra: format(data, 'EEEEE', { locale: ptBR }).toUpperCase(), // D, S, T...
        });
    }
    return dias;
});

const mesAnoFormatado = computed(() => format(dataReferencia.value, 'MMMM yyyy', { locale: ptBR }));

const aulasDoDia = computed(() => {
    if (!Array.isArray(aulas.value)) return [];
    // ATENÇÃO: Aqui usamos 'aula.start' que é o campo que o seu backend manda
    return aulas.value.filter(aula => aula.start && aula.start.startsWith(dataSelecionada.value))
        .sort((a, b) => new Date(a.start) - new Date(b.start));
});

const proximaSemana = () => { dataReferencia.value = addWeeks(dataReferencia.value, 1); };
const semanaAnterior = () => { dataReferencia.value = subWeeks(dataReferencia.value, 1); };
const selecionarDia = (iso) => { dataSelecionada.value = iso; };

const fetchAulas = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/aulas'); // Endpoint correto
        if (Array.isArray(response.data)) {
            aulas.value = response.data;
        } else if (response.data && Array.isArray(response.data.data)) {
            aulas.value = response.data.data;
        } else {
            aulas.value = [];
        }
    } catch (error) {
        console.error("Erro ao buscar aulas:", error);
        aulas.value = [];
    } finally {
        loading.value = false;
    }
};

const atualizar = () => fetchAulas();

// --- REAGENDAMENTO ---

const podeReagendar = (aula) => {
    if (!aula.start) return false;
    const dataAula = new Date(aula.start);
    const agora = new Date();
    const diffHoras = (dataAula - agora) / 36e5;

    // Regra: > 24h antes e status 'agendada' ou 'cancelada'
    const status = aula.extendedProps?.status;
    return (status === 'agendada' || status === 'cancelada') && diffHoras > 24;
};

const abrirReagendamento = (aula) => {
    aulaParaReagendar.value = aula;
    // FIXA a semana de busca na data da aula original
    semanaReagendamento.value = new Date(aula.start);
    modalReagendar.value = true;
    buscarVagas();
};

const fecharReagendamento = () => {
    modalReagendar.value = false;
    aulaParaReagendar.value = null;
    vagasDisponiveis.value = [];
};

// A função de mudar semana foi removida do modal, pois é fixa.

const buscarVagas = async () => {
    loadingVagas.value = true;
    try {
        const dataBase = format(semanaReagendamento.value, 'yyyy-MM-dd');
        // Usa o ID para filtrar
        const response = await axios.get(`/api/aulas/disponiveis-reagendamento?data_base=${dataBase}&aula_id=${aulaParaReagendar.value.id}`);
        vagasDisponiveis.value = response.data;
    } catch (error) {
        console.error(error);
    } finally {
        loadingVagas.value = false;
    }
};

const confirmarReagendamento = async (vaga) => {
    if (!await mostrarConfirmacao(`Confirmar reagendamento para ${vaga.dia_semana} às ${vaga.horario.substring(0,5)}?`)) return;

    try {
        await axios.post(`/api/aulas/${aulaParaReagendar.value.id}/reagendar`, {
            nova_data_hora: vaga.data_hora,
            id_agenda_destino: vaga.id_agenda
        });
        mostrarSucesso("Sucesso! Aula reagendada.");
        fecharReagendamento();
        fetchAulas();
    } catch (error) {
        mostrarErro("Erro: " + (error.response?.data?.message || 'Tente novamente.'));
    }
};

// --- HELPERS ---
// Verifica se há aula usando o campo 'start' que o backend envia
const temAulaNoDia = (iso) => {
    if(!aulas.value) return false;
    return aulas.value.some(aula => aula.start && aula.start.startsWith(iso));
}
const formatarHora = (iso) => iso ? format(parseISO(iso), 'HH:mm') : '--:--';
const formatarDataSimples = (iso) => format(parseISO(iso), "dd/MM", { locale: ptBR });
const formatarDataHora = (iso) => iso ? format(parseISO(iso), "dd/MM 'às' HH:mm") : '';
const formatarDiaMes = (date) => format(new Date(date), 'dd/MM');
const formatarDiaNumero = (dateIso) => format(parseISO(dateIso), 'dd');

const formatarIntervaloSemana = (dataRef) => {
    const inicio = startOfWeek(new Date(dataRef), { weekStartsOn: 0 });
    const fim = endOfWeek(new Date(dataRef), { weekStartsOn: 0 });
    return `${format(inicio, 'dd/MM')} a ${format(fim, 'dd/MM')}`;
};

const statusColor = (status) => {
    if (status === 'realizada') return 'bg-green-500';
    if (status === 'cancelada') return 'bg-red-500';
    if (status === 'reagendada') return 'bg-yellow-500';
    return 'bg-teal-500';
};

const statusBadge = (status) => {
    if (status === 'realizada') return 'text-green-400 bg-green-400/10 border-green-400/20 border';
    if (status === 'cancelada') return 'text-red-400 bg-red-400/10 border-red-400/20 border';
    if (status === 'reagendada') return 'text-yellow-400 bg-yellow-400/10 border-yellow-400/20 border';
    return 'text-teal-400 bg-teal-400/10 border-teal-400/20 border';
};

onMounted(fetchAulas);
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

.slide-up-enter-active, .slide-up-leave-active { transition: all 0.3s ease-out; }
.slide-up-enter-from, .slide-up-leave-to { transform: translateY(100%); opacity: 0; }
.safe-area-pb {
    padding-bottom: env(safe-area-inset-bottom, 20px);
}
</style>
