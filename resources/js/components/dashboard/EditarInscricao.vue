<template>
  <div v-if="loading" class="text-center text-gray-400 mt-10">Carregando dados do contrato...</div>
  <div v-else-if="inscricao" class="space-y-6">

    <div class="flex justify-between items-center">
      <h2 class="text-2xl font-bold text-white">Editar Inscrição: <span class="text-teal-500">{{ inscricao.usuario?.nome || 'N/A' }}</span></h2>
      <router-link :to="{ name: 'listagem-inscricoes', query: inscricao.usuario_id ? { usuario_id: inscricao.usuario_id } : {} }" class="text-sm font-semibold text-teal-500 hover:text-teal-400 flex items-center gap-1">
        <span>&larr;</span> Voltar para a Listagem
      </router-link>
    </div>

    <div v-if="errorMessage" class="mb-4 bg-red-500/20 border border-red-700 text-red-300 text-sm p-3 rounded-lg">
      {{ errorMessage }}
    </div>

    <div v-if="successMessage" class="mb-4 bg-green-500/20 border border-green-700 text-green-300 text-sm p-3 rounded-lg">
      {{ successMessage }}
    </div>

    <form @submit.prevent="submitForm" class="bg-[#151515] p-8 rounded-xl border border-white/10 space-y-6">

      <fieldset class="form-section border-gray-700">
        <legend class="form-legend">Dados do Contrato</legend>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

          <div>
            <label class="block text-xs text-gray-400 mb-1 ml-1">Status do Contrato</label>
            <select v-model="form.status" class="form-input" :class="{'text-red-400': form.status !== 'ativa', 'text-green-400': form.status === 'ativa'}" required>
                <option value="ativa">ATIVA</option>
                <option value="inativa">INATIVA (Suspensa)</option>
                <option value="trancada">TRANCADA (Pausa)</option>
                <option value="cancelada">CANCELADA (Encerrada)</option>
            </select>
            <p v-if="form.status !== 'ativa'" class="text-xs text-red-400 mt-1">Ao salvar, as aulas futuras serão canceladas e as vagas liberadas (exceto status 'trancada').</p>
          </div>

          <div>
            <label class="block text-xs text-gray-400 mb-1 ml-1">Plano Selecionado (Limite: {{ limiteHorarios }}x)</label>
            <select v-model="form.plano_id" class="form-input" required :disabled="saving">
                <option v-for="plano in planos" :key="plano.id" :value="plano.id">
                    {{ plano.nome }} ({{ limiteHorariosPorPlano(plano.id) }}x/semana)
                </option>
            </select>
          </div>

          <div>
            <label class="block text-xs text-gray-400 mb-1 ml-1">Data de Início (Não Editável)</label>
            <input :value="inscricao.data_inicio" type="date" class="form-input opacity-50 cursor-not-allowed" disabled />
          </div>

        </div>
      </fieldset>

      <fieldset class="form-section border-gray-700">
        <legend class="form-legend">Horários Fixos Reservados ({{ form.horarios_agenda_ids.length }} / {{ limiteHorarios }})</legend>
        <p class="text-sm text-gray-400 mb-4">Selecione os novos horários fixos. O sistema checará a disponibilidade da vaga ao salvar.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
          <div
            v-for="horario in horariosAgenda"
            :key="horario.id"
            @click="toggleHorario(horario.id)"

            :class="['p-4 rounded-lg transition-all duration-200',
                     // Desativa cliques se Lotado OU (Limite Atingido E não selecionado)
                     horario.ocupacao >= horario.vagas_totais || (form.horarios_agenda_ids.length >= limiteHorarios && !form.horarios_agenda_ids.includes(horario.id)) ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:border-teal-600',

                     horario.ocupacao >= horario.vagas_totais ? 'bg-red-900/40 border border-red-700' : 'border',

                     form.horarios_agenda_ids.includes(horario.id) ? 'bg-teal-700 border-teal-500 shadow-lg shadow-teal-900/30' : 'bg-[#1a1a1a] border-gray-700']"

            :title="horario.ocupacao >= horario.vagas_totais ? 'Vaga Esgotada' : (form.horarios_agenda_ids.length >= limiteHorarios && !form.horarios_agenda_ids.includes(horario.id) ? `Limite do Plano (${limiteHorarios}) atingido` : 'Clique para selecionar')"
          >
            <div class="flex justify-between items-start">
                <p class="font-semibold text-lg">{{ formatarDiaSemana(horario.dia_semana) }}</p>
                <input type="checkbox" :checked="form.horarios_agenda_ids.includes(horario.id)" :disabled="horario.ocupacao >= horario.vagas_totais || (form.horarios_agenda_ids.length >= limiteHorarios && !form.horarios_agenda_ids.includes(horario.id))"
                    class="h-5 w-5 rounded transition-colors duration-200" :class="[horario.ocupacao >= horario.vagas_totais || (form.horarios_agenda_ids.length >= limiteHorarios && !form.horarios_agenda_ids.includes(horario.id)) ? 'bg-gray-600 cursor-not-allowed' : 'text-teal-600 bg-gray-700 border-gray-600 focus:ring-teal-500']"/>
            </div>

            <p class="text-xl font-bold mt-1">{{ horario.horario_inicio?.substring(0, 5) || 'N/A' }}</p>

            <div class="mt-2 text-xs">
                <p :class="[horario.ocupacao >= horario.vagas_totais ? 'text-red-300' : 'text-gray-300']">
                    Vagas: {{ horario.ocupacao }} / {{ horario.vagas_totais }}
                </p>
                <p v-if="horario.ocupacao >= horario.vagas_totais" class="text-red-300 font-semibold mt-1">LOTADO</p>
            </div>
          </div>
        </div>
      </fieldset>


      <div class="pt-4 border-t border-gray-700 flex justify-end">
        <button type="submit" :disabled="saving || form.horarios_agenda_ids.length !== limiteHorarios"
            :class="[form.horarios_agenda_ids.length !== limiteHorarios ? 'bg-gray-600' : 'bg-teal-700 hover:bg-teal-600']"
            class="text-white font-semibold py-3 px-8 rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-teal-900/20">
          {{ saving ? 'Salvando...' : 'Salvar Alterações' }}
        </button>
      </div>

    </form>
  </div>
  <div v-else class="text-center text-red-400 mt-10">Inscrição não encontrada.</div>
</template>

<script setup>
import { ref, onMounted, reactive, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios'; // <--- CORREÇÃO: Importação padrão do pacote axios

const props = defineProps({ id: { type: [String, Number], required: true } });

const router = useRouter();
const loading = ref(true);
const saving = ref(false);
const inscricao = ref(null);
const planos = ref([]);
const horariosAgenda = ref([]);
const errorMessage = ref('');
const successMessage = ref('');

const form = reactive({
    usuario_id: null,
    plano_id: null,
    status: 'ativa',
    horarios_agenda_ids: [],
});

// --- LÓGICA DE LIMITE ---
const limiteHorarios = computed(() => {
    const planoSelecionado = planos.value.find(p => p.id === form.plano_id);
    const limite = planoSelecionado ? parseInt(planoSelecionado.numero_aulas) : 0;
    return limite > 0 ? limite : 0;
});
const limiteHorariosPorPlano = (planoId) => {
    const plano = planos.value.find(p => p.id === planoId);
    return plano ? parseInt(plano.numero_aulas) : 0;
};

// Função Auxiliar: Adapta a nova estrutura (BelongsToMany) para a antiga (que o Template espera)
// O template espera: v-for="horario" e usa "horario.agenda.dia_semana" e "horario.status"
const adaptarEstruturaParaTemplate = (dadosInscricao) => {
    if (dadosInscricao.horarios_aluno && Array.isArray(dadosInscricao.horarios_aluno)) {
        // Mapeia a lista de Agendas para uma lista de Objetos parecidos com o antigo HorarioAluno
        dadosInscricao.horarios_aluno = dadosInscricao.horarios_aluno.map(agenda => {
            // Se já estiver adaptado (tem a prop .agenda), retorna como está
            if (agenda.agenda) return agenda;

            return {
                id: agenda.pivot ? agenda.pivot.id : null, // ID do vínculo
                status: agenda.pivot ? agenda.pivot.status : 'ativo', // Status do vínculo
                agenda: agenda // Aninha o objeto agenda dentro de uma prop .agenda
            };
        });
    }
    return dadosInscricao;
};

// 1. Fetch de Dados Iniciais
const fetchDados = async () => {
  loading.value = true;
  try {
    const [planosResponse, inscricaoResponse, horariosResponse] = await Promise.all([
        axios.get('/api/planos'),
        axios.get(`/api/inscricoes/${props.id}`),
        axios.get('/api/horarios-agenda')
    ]);

    planos.value = planosResponse.data.data || planosResponse.data;

    // CRÍTICO: Aplica o adaptador nos dados recebidos
    const dataRaw = inscricaoResponse.data.data || inscricaoResponse.data;
    const dataAdaptada = adaptarEstruturaParaTemplate(dataRaw);

    inscricao.value = dataAdaptada;

    horariosAgenda.value = (horariosResponse.data.data || horariosResponse.data)
        .map(h => ({ ...h, ocupacao: h.ocupacao || 0 }))
        .sort((a, b) => a.dia_semana - b.dia_semana);

    // Preencher o formulário
    // Nota: Como adaptamos os dados, agora acessamos h.agenda.id
    Object.assign(form, {
        usuario_id: dataAdaptada.usuario_id,
        plano_id: dataAdaptada.plano_id,
        status: dataAdaptada.status,
        horarios_agenda_ids: dataAdaptada.horarios_aluno.map(h => h.agenda.id),
    });

  } catch (error) {
    console.error("Erro ao buscar dados da inscrição:", error.response || error);
    errorMessage.value = "Erro ao carregar os dados da inscrição. Verifique a rota ou o token.";
  } finally {
    loading.value = false;
  }
};

// 2. Lógica de Seleção de Horários (Toggle)
const toggleHorario = (id) => {
    const horario = horariosAgenda.value.find(h => h.id === id);
    const limite = limiteHorarios.value;

    if (!horario || horario.ocupacao >= horario.vagas_totais) return;

    const index = form.horarios_agenda_ids.indexOf(id);

    if (index > -1) {
        form.horarios_agenda_ids.splice(index, 1);
        errorMessage.value = '';
    } else {
        if (form.horarios_agenda_ids.length >= limite) {
            errorMessage.value = `O limite de horários fixos para o plano (${limite}x) foi atingido.`;
            return;
        }
        form.horarios_agenda_ids.push(id);
        errorMessage.value = '';
    }
};

// 3. Submissão do Formulário
const submitForm = async () => {
  saving.value = true;
  errorMessage.value = '';
  successMessage.value = '';

  if (form.horarios_agenda_ids.length !== limiteHorarios.value) {
    errorMessage.value = `O plano exige a seleção de EXATAMENTE ${limiteHorarios.value} horários.`;
    saving.value = false;
    return;
  }

  const payload = {
      usuario_id: form.usuario_id,
      plano_id: form.plano_id,
      status: form.status,
      horarios_agenda_ids: form.horarios_agenda_ids,
  };

  try {
    const response = await axios.put(`/api/inscricoes/${props.id}`, payload);

    successMessage.value = 'Inscrição atualizada com sucesso!';

    // Atualiza os dados da tela com a resposta fresca (também adaptada)
    const responseData = response.data.data || response.data;
    inscricao.value = adaptarEstruturaParaTemplate(responseData);

    setTimeout(() => {
      const userId = inscricao.value.usuario_id;
      router.push({
          name: 'listagem-inscricoes',
          query: userId ? { usuario_id: userId } : {}
      });
    }, 1500);

  } catch (error) {
    console.error("Erro ao atualizar inscrição:", error.response || error);
    if (error.response && error.response.data && error.response.status === 422) {
        errorMessage.value = error.response.data.message || 'Erro de validação.';
    } else {
        errorMessage.value = 'Falha crítica ao salvar. Verifique o servidor.';
    }
  } finally {
    saving.value = false;
  }
};

// 4. Funções de Utilidade
const formatarDiaSemana = (dia) => {
  const dias = ['Dom', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'];
  return dias[dia === 7 ? 0 : dia];
};

onMounted(fetchDados);
</script>

<style scoped>
.form-input {
  @apply w-full p-3 rounded-lg bg-[#0f1616] text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-600 border border-transparent transition-all;
}
.form-section { @apply border border-gray-700 rounded-lg p-4 pt-2; }
.form-legend { @apply px-2 text-sm font-semibold text-teal-400 -ml-2; }
</style>
