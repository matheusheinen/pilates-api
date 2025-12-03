<template>
  <div class="space-y-6">

    <div v-if="proximaAula" class="bg-[#1e1e1e] rounded-2xl p-5 border border-gray-800 shadow-md">
        <div class="flex justify-between items-start">
            <div>
                <span class="text-xs font-bold text-teal-400 uppercase tracking-wider">Próxima Aula</span>
                <h3 class="text-xl font-bold text-white mt-1 capitalize">{{ formatarDiaExtenso(proximaAula.start) }}</h3>
            </div>
            <div class="bg-teal-600 px-3 py-1 rounded-lg text-white font-bold shadow-sm">
                {{ formatarHora(proximaAula.start) }}
            </div>
        </div>

        </div>

    <div v-else-if="!loading" class="bg-[#1e1e1e] rounded-2xl p-6 text-center border border-gray-800">
        <p class="text-gray-400">Nenhuma aula agendada para breve.</p>
        <router-link :to="{name: 'aluno-agenda'}" class="text-teal-400 text-sm font-bold mt-2 inline-block">Ver Agenda Completa</router-link>
    </div>

    <div>
        <div class="flex justify-between items-end mb-3 px-1">
            <h3 class="text-lg font-bold text-white">Essa Semana</h3>
            <router-link :to="{name: 'aluno-agenda'}" class="text-xs text-teal-400 hover:underline">Ver tudo</router-link>
        </div>

        <div class="space-y-3">
            <div v-for="aula in aulasDaSemana" :key="aula.id" class="bg-[#1e1e1e] p-4 rounded-xl flex items-center gap-4 border border-gray-800">
                <div class="bg-gray-800 w-12 h-12 rounded-lg flex flex-col items-center justify-center text-gray-300">
                    <span class="text-[10px] uppercase font-bold">{{ formatarDiaCurto(aula.start) }}</span>
                    <span class="text-sm font-bold text-white">{{ formatarDiaNumero(aula.start) }}</span>
                </div>
                <div>
                    <p class="text-white font-semibold">{{ formatarHora(aula.start) }} - Pilates</p>
                    <p :class="`text-xs capitalize ${aula.extendedProps?.status === 'realizada' ? 'text-green-400' : 'text-gray-500'}`">
                        {{ aula.extendedProps?.status || 'Agendada' }}
                    </p>
                </div>
            </div>
             <p v-if="aulasDaSemana.length === 0 && !loading" class="text-sm text-gray-500 italic px-1">Sem mais aulas esta semana.</p>
        </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { format, parseISO, isAfter, isSameWeek } from 'date-fns';
import { ptBR } from 'date-fns/locale';

const aulas = ref([]);
const loading = ref(true);

const proximaAula = computed(() => {
    if (!Array.isArray(aulas.value)) return null;
    const agora = new Date();

    // FILTRO: Ignora canceladas E reagendadas
    const futuras = aulas.value
        .filter(a => {
            const status = a.extendedProps?.status;
            return a.start &&
                   isAfter(parseISO(a.start), agora) &&
                   status !== 'cancelada' &&
                   status !== 'reagendada';
        })
        .sort((a, b) => new Date(a.start) - new Date(b.start));

    return futuras.length > 0 ? futuras[0] : null;
});

const aulasDaSemana = computed(() => {
    if (!Array.isArray(aulas.value)) return [];
    const agora = new Date();
    // Filtra aulas que tem 'start' definido e estão na mesma semana
    return aulas.value.filter(a => a.start && isSameWeek(parseISO(a.start), agora, { weekStartsOn: 0 }));
});

const carregarDados = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/api/aulas');

        if (Array.isArray(res.data)) {
            aulas.value = res.data;
        } else if (res.data && Array.isArray(res.data.data)) {
            aulas.value = res.data.data;
        } else {
            aulas.value = [];
        }
    } catch (e) {
        console.error("Erro ao carregar dados", e);
        aulas.value = [];
    } finally {
        loading.value = false;
    }
};

const formatarDiaExtenso = (d) => d ? format(parseISO(d), "EEEE, dd 'de' MMMM", { locale: ptBR }) : '';
const formatarHora = (d) => d ? format(parseISO(d), 'HH:mm') : '--:--';
const formatarDiaCurto = (d) => d ? format(parseISO(d), 'EEE', { locale: ptBR }).replace('.', '') : '';
const formatarDiaNumero = (d) => d ? format(parseISO(d), 'dd') : '';

onMounted(carregarDados);
</script>
