<template>
    <div class="bg-[#151515] min-h-screen text-white p-6">
        <div class="flex items-center gap-4 mb-8 border-b border-gray-800 pb-6">
            <button @click="$router.back()" class="text-gray-400 hover:text-white transition p-2 hover:bg-[#242424] rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </button>
            <div>
                <h1 class="text-2xl font-bold">Matrícula</h1>
                <p class="text-gray-500 text-sm" v-if="cliente">Aluno: <span class="text-white font-medium">{{ cliente.nome }}</span></p>
            </div>
        </div>

        <div v-if="loading" class="flex justify-center items-center py-20">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-[#009088]"></div>
        </div>

        <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <div class="lg:col-span-2 space-y-6">

                <div class="bg-[#242424] p-6 rounded-xl border border-[#333]">
                    <h2 class="text-lg font-semibold text-[#009088] mb-4 flex items-center gap-2">
                        <span class="bg-[#009088] text-white text-xs rounded-full w-6 h-6 flex items-center justify-center font-bold">1</span>
                        Plano e Vigência
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Plano Contratado</label>
                            <select
                                v-model="form.plano_id"
                                @change="resetarHorarios"
                                class="w-full bg-[#1e1e1e] border border-[#444] text-white rounded-lg py-3 px-4 focus:ring-1 focus:ring-[#009088] focus:border-[#009088] outline-none transition shadow-sm">
                                <option value="" disabled>Selecione...</option>
                                <option v-for="plano in planos" :key="plano.id" :value="plano.id">
                                    {{ plano.nome }} ({{ plano.numero_aulas }}x/sem) - R$ {{ formatarMoeda(plano.preco) }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Início das Aulas</label>
                            <input
                                type="date"
                                v-model="form.data_inicio"
                                class="w-full bg-[#1e1e1e] border border-[#444] text-white rounded-lg py-3 px-4 focus:ring-1 focus:ring-[#009088] focus:border-[#009088] outline-none transition shadow-sm">
                        </div>
                    </div>
                </div>

                <div class="bg-[#242424] p-6 rounded-xl border border-[#333] min-h-[400px]">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                        <h2 class="text-lg font-semibold text-[#009088] flex items-center gap-2">
                            <span class="bg-[#009088] text-white text-xs rounded-full w-6 h-6 flex items-center justify-center font-bold">2</span>
                            Horários Fixos
                        </h2>

                        <div v-if="planoSelecionado"
                             class="text-sm px-4 py-1.5 rounded-full border transition-all duration-300 font-medium flex items-center gap-2"
                             :class="statusSelecaoClass">
                             <span v-if="completo">✓</span>
                            {{ form.horarios_agenda_ids.length }} de {{ planoSelecionado.numero_aulas }} horários selecionados
                        </div>
                    </div>

                    <div v-if="!planoSelecionado" class="flex flex-col items-center justify-center h-64 border-2 border-dashed border-[#333] rounded-lg text-gray-500 bg-[#1e1e1e]/50">
                        <p>Selecione um plano acima para liberar a agenda.</p>
                    </div>

                    <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                        <div v-for="horario in horariosDisponiveis" :key="horario.id"
                             @click="toggleHorario(horario)"
                             :class="[
                                'relative border rounded-xl p-4 cursor-pointer transition-all duration-200 select-none flex flex-col justify-between min-h-[100px]',
                                isChecked(horario.id)
                                    ? 'bg-[#009088]/20 border-[#009088] ring-1 ring-[#009088]'
                                    : 'bg-[#1e1e1e] border-[#333] hover:border-gray-500 hover:bg-[#262626]',
                                isDisabled(horario.id) ? 'opacity-40 cursor-not-allowed grayscale' : ''
                             ]"
                        >
                            <div class="flex justify-between items-start mb-2">
                                <span class="font-bold text-sm text-white uppercase tracking-wider">{{ formatarDia(horario.dia_semana) }}</span>

                                <div class="text-[10px] font-bold px-2 py-0.5 rounded border" :class="getBadgeClass(horario)">
                                    {{ horario.inscricoes_count }}/{{ horario.vagas_totais }}
                                </div>
                            </div>

                            <div class="text-2xl font-mono text-gray-200 font-light tracking-wide">
                                {{ horario.horario_inicio.substring(0, 5) }}
                            </div>

                            <div v-if="isChecked(horario.id)" class="absolute bottom-2 right-2 text-[#009088]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-1">
                <div class="bg-[#242424] p-6 rounded-xl border border-[#333] sticky top-6 shadow-xl">
                    <h3 class="font-bold text-white mb-6 text-lg border-b border-gray-700 pb-4">Resumo</h3>

                    <div class="space-y-4 mb-8 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Aluno</span>
                            <span class="text-white font-medium text-right">{{ cliente.nome }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Plano</span>
                            <span class="text-white font-medium text-right">{{ planoSelecionado?.nome || '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-400">Horários</span>
                            <span :class="completo ? 'text-green-400 font-bold' : 'text-gray-500'">
                                {{ form.horarios_agenda_ids.length }} selecionados
                            </span>
                        </div>
                        <div class="pt-4 border-t border-gray-700 flex justify-between items-center">
                            <span class="text-gray-400">Total Mensal</span>
                            <span class="text-[#009088] font-bold text-xl">R$ {{ formatarMoeda(planoSelecionado?.preco) }}</span>
                        </div>
                    </div>

                    <div v-if="erroApi" class="mb-4 p-3 bg-red-900/20 border border-red-800/50 text-red-300 rounded-lg text-xs">
                        {{ erroApi }}
                    </div>

                    <button
                        @click="salvarInscricao"
                        class="w-full bg-[#009088] hover:bg-[#007972] disabled:bg-gray-700 disabled:text-gray-500 disabled:cursor-not-allowed text-white font-bold py-3 rounded-lg shadow-lg transition duration-200 flex justify-center items-center gap-2"
                        :disabled="processando || !formValid">
                        <span v-if="processando" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
                        {{ processando ? 'Processando...' : 'Confirmar Matrícula' }}
                    </button>

                    <button @click="$router.back()" class="w-full mt-3 text-gray-500 hover:text-white text-sm py-2 transition">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const router = useRouter();
const clienteId = route.params.id;

// Estados
const cliente = ref({});
const planos = ref([]);
const horariosDisponiveis = ref([]);
const loading = ref(true);
const processando = ref(false);
const erroApi = ref('');

// Formulário
const form = reactive({
    plano_id: '',
    data_inicio: new Date().toISOString().split('T')[0], // Data de hoje como padrão
    horarios_agenda_ids: []
});

// --- Carregamento Inicial ---
onMounted(async () => {
    try {
        // Carrega dados em paralelo
        const [resCliente, resPlanos, resHorarios] = await Promise.all([
            axios.get(`/api/usuarios/${clienteId}`),
            axios.get('/api/planos'),
            axios.get('/api/horarios-agenda/disponiveis')
        ]);

        cliente.value = resCliente.data;
        planos.value = resPlanos.data;
        horariosDisponiveis.value = resHorarios.data;

        // Se já tiver inscrição ativa, alerta (opcional)
        // Assumindo que 'inscricoes' vem no objeto cliente (se sua API retornar isso)
        if (cliente.value.inscricoes && cliente.value.inscricoes.some(i => i.status === 'ativa')) {
            // Se quiser bloquear:
            // alert("Este aluno já possui uma matrícula ativa.");
            // router.back();
        }

    } catch (error) {
        console.error("Erro ao carregar dados:", error);
        erroApi.value = "Falha ao carregar informações. Verifique sua conexão.";
    } finally {
        loading.value = false;
    }
});

// --- Lógica Computada ---
const planoSelecionado = computed(() => planos.value.find(p => p.id === form.plano_id));

const completo = computed(() => {
    if (!planoSelecionado.value) return false;
    return form.horarios_agenda_ids.length === planoSelecionado.value.numero_aulas;
});

const formValid = computed(() => {
    return form.plano_id && form.data_inicio && completo.value;
});

const statusSelecaoClass = computed(() => {
    if (completo.value) return 'bg-green-900/30 text-green-400 border-green-800';
    return 'bg-yellow-900/30 text-yellow-400 border-yellow-800';
});

// --- Ações ---
const resetarHorarios = () => {
    form.horarios_agenda_ids = [];
};

const isChecked = (id) => form.horarios_agenda_ids.includes(id);

const isDisabled = (id) => {
    if (!planoSelecionado.value) return true; // Bloqueia se não tem plano
    // Bloqueia se já atingiu o limite do plano E o horário atual não está selecionado
    if (completo.value && !isChecked(id)) return true;
    return false;
};

const toggleHorario = (horario) => {
    if (isDisabled(horario.id)) return;

    const index = form.horarios_agenda_ids.indexOf(horario.id);
    if (index === -1) {
        form.horarios_agenda_ids.push(horario.id);
    } else {
        form.horarios_agenda_ids.splice(index, 1);
    }
};

const salvarInscricao = async () => {
    processando.value = true;
    erroApi.value = '';

    try {
        await axios.post('/api/inscricoes', {
            usuario_id: clienteId,
            plano_id: form.plano_id,
            data_inicio: form.data_inicio,
            horarios_agenda_ids: form.horarios_agenda_ids
        });

        alert('Matrícula realizada com sucesso!');
        router.push({ name: 'detalhes-cliente', params: { id: clienteId } });

    } catch (error) {
        if (error.response?.data?.message) {
            erroApi.value = error.response.data.message;
        } else {
            erroApi.value = "Erro ao processar a matrícula.";
        }
    } finally {
        processando.value = false;
    }
};

// --- Helpers de Formatação ---
const formatarDia = (dia) => {
    const dias = ['','Segunda','Terça','Quarta','Quinta','Sexta','Sábado'];
    return dias[dia] || 'Dia';
};

const formatarMoeda = (val) => {
    if (!val) return '0,00';
    return parseFloat(val).toFixed(2).replace('.', ',');
};

const getBadgeClass = (horario) => {
    // Evita divisão por zero se vagas_totais for null ou 0
    const total = horario.vagas_totais || 3;
    const ocupacao = (horario.inscricoes_count || 0) / total;

    if (ocupacao >= 1) return 'bg-red-900/50 text-red-200 border-red-800'; // Cheio
    if (ocupacao > 0.6) return 'bg-yellow-900/50 text-yellow-200 border-yellow-800'; // Quase cheio
    return 'bg-gray-700 text-gray-300 border-gray-600'; // Livre
};
</script>
