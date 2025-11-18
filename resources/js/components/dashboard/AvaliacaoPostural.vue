<template>
  <div v-if="loading" class="text-center text-gray-400">Carregando ficha de avaliação...</div>
  <div v-else class="bg-[#151515] p-6 rounded-xl text-white">
    <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-6 gap-4">
      <div>
        <h2 class="text-2xl font-bold text-white">Ficha de Avaliação Postural</h2>
        <p v-if="cliente.nome" class="text-teal-400">{{ cliente.nome }}</p>
      </div>
      <router-link :to="{ name: 'detalhes-cliente', params: { id: id } }" class="text-sm font-semibold text-teal-500 hover:text-teal-400 self-end sm:self-center">
        &larr; Voltar para os Detalhes
      </router-link>
    </div>

    <div v-if="successMessage" class="mb-4 bg-green-500/20 border border-green-700 text-green-300 text-sm p-3 rounded-lg">
      {{ successMessage }}
    </div>
    <div v-if="errorMessage" class="mb-4 bg-red-500/20 border border-red-700 text-red-300 text-sm p-3 rounded-lg">
      {{ errorMessage }}
    </div>

    <form @submit.prevent="salvarAvaliacao" class="space-y-8">

      <fieldset class="form-section border-teal-900/50">
          <legend class="form-legend text-teal-300">Dados Clínicos Atuais</legend>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
              <div class="form-group">
                  <label class="form-label">Peso (kg)</label>
                  <input v-model="ficha.peso" type="number" step="0.1" class="form-input" placeholder="Ex: 70.5">
              </div>
              <div class="form-group">
                  <label class="form-label">Altura (m)</label>
                  <input v-model="ficha.altura" type="number" step="0.01" class="form-input" placeholder="Ex: 1.75">
              </div>
          </div>
          <div class="grid grid-cols-1 gap-4">
              <div class="form-group">
                  <label class="form-label">Queixa Principal / Objetivo</label>
                  <textarea v-model="ficha.queixa_principal" rows="2" class="form-input" placeholder="Descreva a dor ou objetivo atual do aluno..."></textarea>
              </div>
              <div class="form-group">
                  <label class="form-label">Diagnóstico Clínico / Patologia</label>
                  <textarea v-model="ficha.diagnostico_clinico" rows="2" class="form-input" placeholder="Ex: Hérnia de Disco L4-L5, Escoliose, etc."></textarea>
              </div>
          </div>
      </fieldset>

      <div class="form-grid-4-cols !gap-y-4">
            <div class="form-group">
              <label class="form-label">Data da Avaliação *</label>
              <input v-model="ficha.data_avaliacao" type="date" class="form-input" required>
          </div>

          <div class="form-group">
            <label class="form-label">Anexar Exame/Laudo (Opcional - PDF ou Imagem)</label>
            <input
              type="file"
              @change="handleFileChange"
              accept="image/*,.pdf"
              class="form-input file:mr-4 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-teal-700 file:text-white hover:file:bg-teal-600 cursor-pointer"
            >
            <span v-if="selectedFile" class="text-xs text-gray-400 mt-1">Arquivo selecionado: {{ selectedFile.name }}</span>
            <div v-if="ficha.caminho_anexo && !selectedFile" class="text-xs text-gray-400 mt-1">
                Anexo existente: <a :href="`/storage/${ficha.caminho_anexo.replace('public/', '')}`" target="_blank" class="text-teal-400 hover:underline">{{ ficha.caminho_anexo.split('/').pop() }}</a>
                <button type="button" @click="removerAnexoExistente" class="ml-2 text-red-500 hover:text-red-400 text-xs">(Remover)</button>
              </div>
          </div>
      </div>


      <fieldset class="form-section">
        <legend class="form-legend">Vista Anterior</legend>
        <div class="form-grid-1-cols">
          <div class="form-group"><label class="form-label">Cabeça</label><div class="radio-group"><label><input type="radio" v-model="ficha.anterior_cabeca" value="Alinhada" class="form-radio"> Alinhada</label><label><input type="radio" v-model="ficha.anterior_cabeca" value="Inclinada à D" class="form-radio"> Inclinada à D</label><label><input type="radio" v-model="ficha.anterior_cabeca" value="Inclinada à E" class="form-radio"> Inclinada à E</label><label><input type="radio" v-model="ficha.anterior_cabeca" value="Rodada à D" class="form-radio"> Rodada à D</label><label><input type="radio" v-model="ficha.anterior_cabeca" value="Rodada à E" class="form-radio"> Rodada à E</label></div></div>
          <div class="form-group"><label class="form-label">Altura dos Ombros</label><div class="radio-group"><label><input type="radio" v-model="ficha.anterior_ombros_altura" value="Simétricos" class="form-radio"> Simétricos</label><label><input type="radio" v-model="ficha.anterior_ombros_altura" value="Direito + alto" class="form-radio"> D + alto</label><label><input type="radio" v-model="ficha.anterior_ombros_altura" value="Esquerdo + alto" class="form-radio"> E + alto</label></div></div>
          <div class="form-group"><label class="form-label">Altura das Mãos</label><div class="radio-group"><label><input type="radio" v-model="ficha.anterior_maos_altura" value="Simétricas" class="form-radio"> Simétricas</label><label><input type="radio" v-model="ficha.anterior_maos_altura" value="Direita + alta" class="form-radio"> D + alta</label><label><input type="radio" v-model="ficha.anterior_maos_altura" value="Esquerda + alta" class="form-radio"> E + alta</label></div></div>
          <div class="form-group"><label class="form-label">Rotação do Tronco</label><div class="radio-group"><label><input type="radio" v-model="ficha.anterior_tronco_rotacao" value="Ausente" class="form-radio"> Ausente</label><label><input type="radio" v-model="ficha.anterior_tronco_rotacao" value="À direita" class="form-radio"> À direita</label><label><input type="radio" v-model="ficha.anterior_tronco_rotacao" value="À esquerda" class="form-radio"> À esquerda</label></div></div>
          <div class="form-group"><label class="form-label">Ângulo de Tales</label><div class="radio-group"><label><input type="radio" v-model="ficha.anterior_angulo_tales" value="Simétrico" class="form-radio"> Simétrico</label><label><input type="radio" v-model="ficha.anterior_angulo_tales" value="Maior à direita" class="form-radio"> Maior à D</label><label><input type="radio" v-model="ficha.anterior_angulo_tales" value="Maior à esquerda" class="form-radio"> Maior à E</label></div></div>
          <div class="form-group"><label class="form-label">Cicatriz Umbilical</label><div class="radio-group"><label><input type="radio" v-model="ficha.anterior_cicatriz_umbilical" value="Alinhada" class="form-radio"> Alinhada</label><label><input type="radio" v-model="ficha.anterior_cicatriz_umbilical" value="Desvio à D" class="form-radio"> Desvio à D</label><label><input type="radio" v-model="ficha.anterior_cicatriz_umbilical" value="Desvio à E" class="form-radio"> Desvio à E</label></div></div>
          <div class="form-group"><label class="form-label">Altura das C. Ilíacas</label><div class="radio-group"><label><input type="radio" v-model="ficha.anterior_iliacas_altura" value="Simétricas" class="form-radio"> Simétricas</label><label><input type="radio" v-model="ficha.anterior_iliacas_altura" value="Direita + alta" class="form-radio"> D + alta</label><label><input type="radio" v-model="ficha.anterior_iliacas_altura" value="Esquerda + alta" class="form-radio"> E + alta</label></div></div>
          <div class="form-group"><label class="form-label">Joelhos</label><div class="radio-group"><label><input type="radio" v-model="ficha.anterior_joelhos" value="Normal" class="form-radio"> Normal</label><label><input type="radio" v-model="ficha.anterior_joelhos" value="Valgo" class="form-radio"> Valgo</label><label><input type="radio" v-model="ficha.anterior_joelhos" value="Varo" class="form-radio"> Varo</label></div></div>
          <div class="form-group"><label class="form-label">Tornozelos</label><div class="radio-group"><label><input type="radio" v-model="ficha.anterior_tornozelos" value="Normal" class="form-radio"> Normal</label><label><input type="radio" v-model="ficha.anterior_tornozelos" value="Valgo" class="form-radio"> Valgo</label><label><input type="radio" v-model="ficha.anterior_tornozelos" value="Varo" class="form-radio"> Varo</label></div></div>
          <div class="form-group"><label class="form-label">Pés</label><div class="radio-group"><label><input type="radio" v-model="ficha.anterior_pes" value="Normal" class="form-radio"> Normal</label><label><input type="radio" v-model="ficha.anterior_pes" value="Planos" class="form-radio"> Planos</label><label><input type="radio" v-model="ficha.anterior_pes" value="Cavos" class="form-radio"> Cavos</label></div></div>
        </div>
      </fieldset>

      <fieldset class="form-section">
        <legend class="form-legend">Vista Posterior</legend>
        <div class="form-grid-1-cols">
          <div class="form-group"><label class="form-label">Altura das Escápulas</label><div class="radio-group"><label><input type="radio" v-model="ficha.posterior_escapulas_altura" value="Simétricas" class="form-radio"> Simétricas</label><label><input type="radio" v-model="ficha.posterior_escapulas_altura" value="Direita + alta" class="form-radio"> D + alta</label><label><input type="radio" v-model="ficha.posterior_escapulas_altura" value="Esquerda + alta" class="form-radio"> E + alta</label></div></div>
          <div class="form-group"><label class="form-label">Escápula Alada</label><div class="radio-group"><label><input type="radio" v-model="ficha.posterior_escapula_alada" value="Ausente" class="form-radio"> Ausente</label><label><input type="radio" v-model="ficha.posterior_escapula_alada" value="À direita" class="form-radio"> À direita</label><label><input type="radio" v-model="ficha.posterior_escapula_alada" value="À esquerda" class="form-radio"> À esquerda</label><label><input type="radio" v-model="ficha.posterior_escapula_alada" value="Bilateral > D" class="form-radio"> Bilateral > D</label><label><input type="radio" v-model="ficha.posterior_escapula_alada" value="Bilateral > E" class="form-radio"> Bilateral > E</label></div></div>
          <div class="form-group"><label class="form-label">Gibosidade Torácica</label><div class="radio-group"><label><input type="radio" v-model="ficha.posterior_gibosidade_toracica" value="Ausente" class="form-radio"> Ausente</label><label><input type="radio" v-model="ficha.posterior_gibosidade_toracica" value="À direita" class="form-radio"> À direita</label><label><input type="radio" v-model="ficha.posterior_gibosidade_toracica" value="À esquerda" class="form-radio"> À esquerda</label></div></div>
          <div class="form-group"><label class="form-label">Pregas Glúteas</label><div class="radio-group"><label><input type="radio" v-model="ficha.posterior_pregas_gluteas" value="Simétricas" class="form-radio"> Simétricas</label><label><input type="radio" v-model="ficha.posterior_pregas_gluteas" value="Direita + alta" class="form-radio"> D + alta</label><label><input type="radio" v-model="ficha.posterior_pregas_gluteas" value="Esquerda + alta" class="form-radio"> E + alta</label></div></div>
          <div class="form-group"><label class="form-label">Pregas Poplíteas</label><div class="radio-group"><label><input type="radio" v-model="ficha.posterior_pregas_popliteas" value="Simétricas" class="form-radio"> Simétricas</label><label><input type="radio" v-model="ficha.posterior_pregas_popliteas" value="Direita + alta" class="form-radio"> D + alta</label><label><input type="radio" v-model="ficha.posterior_pregas_popliteas" value="Esquerda + alta" class="form-radio"> E + alta</label></div></div>
          <div class="form-group"><label class="form-label">Coluna Lombar c/ Concavidade</label><div class="radio-group"><label><input type="radio" v-model="ficha.posterior_lombar_concavidade" value="Ausente" class="form-radio"> Ausente</label><label><input type="radio" v-model="ficha.posterior_lombar_concavidade" value="À direita" class="form-radio"> À direita</label><label><input type="radio" v-model="ficha.posterior_lombar_concavidade" value="À esquerda" class="form-radio"> À esquerda</label></div></div>
          <div class="form-group"><label class="form-label">Coluna Torácica c/ Concavidade</label><div class="radio-group"><label><input type="radio" v-model="ficha.posterior_toracica_concavidade" value="Ausente" class="form-radio"> Ausente</label><label><input type="radio" v-model="ficha.posterior_toracica_concavidade" value="À direita" class="form-radio"> À direita</label><label><input type="radio" v-model="ficha.posterior_toracica_concavidade" value="À esquerda" class="form-radio"> À esquerda</label></div></div>
          <div class="form-group"><label class="form-label">Coluna Cervical c/ Concavidade</label><div class="radio-group"><label><input type="radio" v-model="ficha.posterior_cervical_concavidade" value="Ausente" class="form-radio"> Ausente</label><label><input type="radio" v-model="ficha.posterior_cervical_concavidade" value="À direita" class="form-radio"> À direita</label><label><input type="radio" v-model="ficha.posterior_cervical_concavidade" value="À esquerda" class="form-radio"> À esquerda</label></div></div>
        </div>
      </fieldset>

      <fieldset class="form-section">
        <legend class="form-legend">Vista Lateral Direita</legend>
        <div class="form-grid-1-cols">
            <div class="form-group"><label class="form-label">Cabeça</label><div class="radio-group"><label><input type="radio" v-model="ficha.lateral_cabeca" value="Normal" class="form-radio"> Normal</label><label><input type="radio" v-model="ficha.lateral_cabeca" value="Anteriorizada" class="form-radio"> Anteriorizada</label><label><input type="radio" v-model="ficha.lateral_cabeca" value="Posteriorizada" class="form-radio"> Posteriorizada</label></div></div>
            <div class="form-group"><label class="form-label">Coluna Cervical</label><div class="radio-group"><label><input type="radio" v-model="ficha.lateral_cervical" value="Normal" class="form-radio"> Normal</label><label><input type="radio" v-model="ficha.lateral_cervical" value="Hiperlordose" class="form-radio"> Hiperlordose</label><label><input type="radio" v-model="ficha.lateral_cervical" value="Retificada" class="form-radio"> Retificada</label></div></div>
            <div class="form-group"><label class="form-label">Ombro</label><div class="radio-group"><label><input type="radio" v-model="ficha.lateral_ombro" value="Normal" class="form-radio"> Normal</label><label><input type="radio" v-model="ficha.lateral_ombro" value="Protusos" class="form-radio"> Protusos</label><label><input type="radio" v-model="ficha.lateral_ombro" value="Retraídos" class="form-radio"> Retraídos</label></div></div>
            <div class="form-group"><label class="form-label">Membro Superior Dir.</label><div class="radio-group"><label><input type="radio" v-model="ficha.lateral_membro_superior" value="Normal" class="form-radio"> Normal</label><label><input type="radio" v-model="ficha.lateral_membro_superior" value="Anteriorizado" class="form-radio"> Anteriorizado</label><label><input type="radio" v-model="ficha.lateral_membro_superior" value="Posteriorizado" class="form-radio"> Posteriorizado</label></div></div>
            <div class="form-group"><label class="form-label">Coluna Torácica</label><div class="radio-group"><label><input type="radio" v-model="ficha.lateral_toracica" value="Normal" class="form-radio"> Normal</label><label><input type="radio" v-model="ficha.lateral_toracica" value="Cifose Aumentada" class="form-radio"> Cifose Aumentada</label><label><input type="radio" v-model="ficha.lateral_toracica" value="Plano" class="form-radio"> Plano</label></div></div>
            <div class="form-group"><label class="form-label">Rotação de Tronco</label><div class="radio-group"><label><input type="radio" v-model="ficha.lateral_tronco_rotacao" value="Normal" class="form-radio"> Normal</label><label><input type="radio" v-model="ficha.lateral_tronco_rotacao" value="Direita" class="form-radio"> Direita</label><label><input type="radio" v-model="ficha.lateral_tronco_rotacao" value="Esquerda" class="form-radio"> Esquerda</label></div></div>
            <div class="form-group"><label class="form-label">Abdômen</label><div class="radio-group"><label><input type="radio" v-model="ficha.lateral_abdomen" value="Normal" class="form-radio"> Normal</label><label><input type="radio" v-model="ficha.lateral_abdomen" value="Protuso" class="form-radio"> Protuso</label><label><input type="radio" v-model="ficha.lateral_abdomen" value="Ptose" class="form-radio"> Ptose</label></div></div>
            <div class="form-group"><label class="form-label">Coluna Lombar</label><div class="radio-group"><label><input type="radio" v-model="ficha.lateral_lombar" value="Normal" class="form-radio"> Normal</label><label><input type="radio" v-model="ficha.lateral_lombar" value="Hiperlordose" class="form-radio"> Hiperlordose</label><label><input type="radio" v-model="ficha.lateral_lombar" value="Retificada" class="form-radio"> Retificada</label></div></div>
            <div class="form-group"><label class="form-label">Pelve</label><div class="radio-group"><label><input type="radio" v-model="ficha.lateral_pelve" value="Normal" class="form-radio"> Normal</label><label><input type="radio" v-model="ficha.lateral_pelve" value="Anteroversão" class="form-radio"> Anteroversão</label><label><input type="radio" v-model="ficha.lateral_pelve" value="Retroversão" class="form-radio"> Retroversão</label></div></div>
            <div class="form-group"><label class="form-label">Quadril</label><div class="radio-group"><label><input type="radio" v-model="ficha.lateral_quadril" value="Normal" class="form-radio"> Normal</label><label><input type="radio" v-model="ficha.lateral_quadril" value="Flexão" class="form-radio"> Flexão</label><label><input type="radio" v-model="ficha.lateral_quadril" value="Extensão" class="form-radio"> Extensão</label></div></div>
            <div class="form-group"><label class="form-label">Joelho</label><div class="radio-group"><label><input type="radio" v-model="ficha.lateral_joelho" value="Normal" class="form-radio"> Normal</label><label><input type="radio" v-model="ficha.lateral_joelho" value="Recurvado" class="form-radio"> Recurvado</label><label><input type="radio" v-model="ficha.lateral_joelho" value="Flexo" class="form-radio"> Flexo</label></div></div>
        </div>
      </fieldset>

      <fieldset class="form-section">
        <legend class="form-legend">Observações</legend>
        <div class="form-grid-1-cols">
          <div class="form-group">
            <label for="observacoes" class="form-label">Observações Adicionais</label>
            <textarea id="observacoes" v-model="ficha.observacoes" rows="4" class="form-input !min-h-[100px]"></textarea>
            <span class="form-hint">Observações gerais, feedback do aluno, etc. (Opcional)</span>
          </div>
        </div>
      </fieldset>

      <div class="pt-4 flex justify-end">
        <button type="submit" :disabled="saving" class="w-full sm:w-auto px-6 py-2 rounded-lg bg-teal-700 hover:bg-teal-600 font-semibold transition-colors disabled:opacity-50">
          {{ saving ? 'Salvando...' : 'Salvar Avaliação' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import { useRoute, useRouter } from 'vue-router';

const props = defineProps({
  id: {
    type: [String, Number],
    required: true
  }
});

const route = useRoute();
const router = useRouter();

const loading = ref(true);
const saving = ref(false);
const cliente = ref({});

// Reactive Object atualizado com os novos campos clínicos
const ficha = reactive({
  usuario_id: props.id,
  data_avaliacao: new Date().toISOString().split('T')[0],
  // Campos Clínicos Novos
  peso: null,
  altura: null,
  queixa_principal: null,
  diagnostico_clinico: null,
  // Campos de Avaliação Visual
  anterior_cabeca: null, anterior_ombros_altura: null, anterior_maos_altura: null,
  anterior_tronco_rotacao: null, anterior_angulo_tales: null, anterior_cicatriz_umbilical: null,
  anterior_iliacas_altura: null, anterior_joelhos: null, anterior_tornozelos: null, anterior_pes: null,
  posterior_escapulas_altura: null, posterior_escapula_alada: null, posterior_gibosidade_toracica: null,
  posterior_pregas_gluteas: null, posterior_pregas_popliteas: null, posterior_lombar_concavidade: null,
  posterior_toracica_concavidade: null, posterior_cervical_concavidade: null,
  lateral_cabeca: null, lateral_cervical: null, lateral_ombro: null, lateral_membro_superior: null,
  lateral_toracica: null, lateral_tronco_rotacao: null, lateral_abdomen: null, lateral_lombar: null,
  lateral_pelve: null, lateral_quadril: null, lateral_joelho: null,
  caminho_anexo: null,
  observacoes: null
});

const selectedFile = ref(null);
const successMessage = ref('');
const errorMessage = ref('');

const handleFileChange = (event) => {
  selectedFile.value = event.target.files[0] || null;
  if (selectedFile.value) {
      ficha.caminho_anexo = null;
  }
};

const removerAnexoExistente = async () => {
    if (confirm('Tem certeza que deseja remover o anexo existente? Esta ação não pode ser desfeita.')) {
      ficha.caminho_anexo = null;
      alert('Anexo removido (visualmente). Salve a ficha para confirmar a remoção no servidor.');
    }
};

const fetchAvaliacao = async () => {
  loading.value = true;
  successMessage.value = '';
  errorMessage.value = '';
  try {
    const clienteResponse = await axios.get(`/api/usuarios/${props.id}`);
    cliente.value = clienteResponse.data;

    // Busca se já existe uma avaliação para editar OU a última para usar de base
    // Ajuste aqui conforme sua lógica de API (se é edição ou criação)
    const avaliacaoResponse = await axios.get(`/api/avaliacoes-posturais?usuario_id=${props.id}&latest=true`);

    if (avaliacaoResponse.data) {
        Object.keys(ficha).forEach(key => {
            if (avaliacaoResponse.data[key] !== undefined) {
                ficha[key] = avaliacaoResponse.data[key];
            }
        });
        // Se a API retornou um ID, significa que estamos EDITANDO uma existente.
        // Se a intenção for sempre criar nova, você deve limpar o ID aqui.
        if (avaliacaoResponse.data.id) {
            ficha.id = avaliacaoResponse.data.id;
        }
    }

    ficha.usuario_id = props.id;
    ficha.data_avaliacao = ficha.data_avaliacao ? ficha.data_avaliacao.split('T')[0] : new Date().toISOString().split('T')[0];

  } catch (error) {
    if (error.response && error.response.status !== 404) {
      errorMessage.value = "Erro ao carregar dados da avaliação.";
      console.error("Erro ao carregar dados:", error);
    } else {
      console.log("Nenhuma avaliação anterior encontrada para este usuário.");
    }
  } finally {
    loading.value = false;
  }
};

const salvarAvaliacao = async () => {
  saving.value = true;
  successMessage.value = '';
  errorMessage.value = '';

  const dataToSubmit = new FormData();

  Object.keys(ficha).forEach(key => {
    if (key !== 'id' && typeof ficha[key] !== 'object' || ficha[key] === null) {
      dataToSubmit.append(key, ficha[key] ?? '');
    }
  });

  if (selectedFile.value) {
    dataToSubmit.append('anexo_exame', selectedFile.value);
  }
  else if (ficha.caminho_anexo === null && ficha.id) {
      dataToSubmit.append('remover_anexo', 'true');
  }

  try {
    let response;
    if (ficha.id) {
        dataToSubmit.append('_method', 'PUT');
        response = await axios.post(`/api/avaliacoes-posturais/${ficha.id}`, dataToSubmit, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
    } else {
        response = await axios.post('/api/avaliacoes-posturais', dataToSubmit, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        ficha.id = response.data.id;
    }

    ficha.caminho_anexo = response.data.caminho_anexo || null;
    selectedFile.value = null;

    successMessage.value = 'Ficha de avaliação salva com sucesso!';

    setTimeout(() => {
        router.push({ name: 'detalhes-cliente', params: { id: props.id } });
    }, 2000);

  } catch (error) {
     if (error.response && error.response.data.errors) {
       const validationErrors = error.response.data.errors;
       const firstErrorKey = Object.keys(validationErrors)[0];
       errorMessage.value = validationErrors[firstErrorKey][0];
     } else {
        errorMessage.value = "Ocorreu um erro ao salvar a ficha.";
     }
    console.error("Erro ao salvar avaliação:", error.response?.data || error);
  } finally {
      saving.value = false;
  }
};

onMounted(fetchAvaliacao);
</script>

<style>
.form-section { @apply border border-gray-700 rounded-lg p-4 pt-2; }
.form-legend { @apply px-2 text-sm font-semibold text-teal-400 -ml-2; }
.form-grid-4-cols { @apply grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-8; }
.form-grid-1-col { @apply grid grid-cols-1 gap-x-8 gap-y-6; }
.form-group { @apply flex flex-col; }
.form-label { @apply text-sm font-medium text-gray-300 mb-2; }
.radio-group { @apply flex flex-wrap gap-x-6 gap-y-3 items-center bg-[#0f1616] p-4 rounded-lg min-h-[60px]; }
.radio-group label { @apply flex items-center text-sm cursor-pointer whitespace-nowrap; }
.form-radio { @apply w-5 h-5 text-teal-600 bg-gray-700 border-gray-600 focus:ring-teal-500 focus:ring-2 mr-2; }
.form-input { @apply w-full p-3 rounded-lg bg-[#0f1616] text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-600; }
</style>
