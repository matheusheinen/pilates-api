<template>
  <div v-if="loading" class="text-center text-gray-400 mt-10">Carregando dados...</div>
  <div v-else-if="cliente" class="space-y-6">

    <div class="flex justify-between items-center">
      <h2 class="text-2xl font-bold text-white">Editar Cadastro: <span class="text-teal-500">{{ cliente.nome }}</span></h2>
      <router-link :to="{ name: 'detalhes-cliente', params: { id: props.id } }" class="text-sm font-semibold text-teal-500 hover:text-teal-400 flex items-center gap-1">
        <span>&larr;</span> Voltar
      </router-link>
    </div>

    <form @submit.prevent="submitForm" class="bg-[#151515] p-8 rounded-xl border border-white/10 space-y-6">

      <div v-if="errorMessage" class="bg-red-500/20 border border-red-700 text-red-300 text-sm p-3 rounded-lg">
          {{ errorMessage }}
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-xs text-gray-400 mb-1 ml-1">Nome Completo</label>
          <input v-model="form.nome" type="text" class="form-input" required />
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1 ml-1">Email</label>
          <input v-model="form.email" type="email" class="form-input" required />
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
         <div>
          <label class="block text-xs text-gray-400 mb-1 ml-1">Celular</label>
          <input
            v-model="form.celular"
            @input="formatarCelular"
            maxlength="15"
            type="text"
            class="form-input"
          />
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1 ml-1">Profissão</label>
          <input v-model="form.profissao" type="text" class="form-input" />
        </div>
      </div>

      <div class="grid grid-cols-1 gap-6">
        <div>
          <label class="block text-xs text-gray-400 mb-1 ml-1">Data de Nascimento</label>
          <input v-model="form.data_nascimento" type="date" class="form-input" />
        </div>
      </div>

      <div class="p-4 border border-gray-700 rounded-lg bg-[#101010]">
        <h4 class="text-sm font-semibold text-teal-500 mb-4">Alterar Senha (Opcional)</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-xs text-gray-400 mb-1 ml-1">Nova Senha (Min 8 carac.)</label>
                <input
                    v-model="form.senha"
                    type="password"
                    class="form-input border-gray-600 placeholder-gray-600 focus:border-teal-500"
                    placeholder="Deixe vazio para manter a atual"
                    minlength="8"
                />
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1 ml-1">Confirmar Nova Senha</label>
                <input
                    v-model="form.confirmarSenha"
                    type="password"
                    class="form-input border-gray-600 placeholder-gray-600 focus:border-teal-500"
                    placeholder="Repita a nova senha"
                    :disabled="!form.senha"
                />
            </div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="text-xs text-gray-400 ml-1">Lateralidade</label>
                <div class="flex items-center space-x-6 mt-1 p-3 rounded-lg bg-[#0f1616] border border-transparent focus-within:border-teal-600 transition-colors">
                <label class="flex items-center cursor-pointer hover:text-teal-400 transition-colors">
                    <input type="radio" v-model="form.lateralidade" value="destro" class="form-radio">
                    <span class="ml-2 text-sm">Destro</span>
                </label>
                <label class="flex items-center cursor-pointer hover:text-teal-400 transition-colors">
                    <input type="radio" v-model="form.lateralidade" value="canhoto" class="form-radio">
                    <span class="ml-2 text-sm">Canhoto</span>
                </label>
                </div>
            </div>

            <div>
                <label class="text-xs text-gray-400 ml-1">Gênero</label>
                <div class="flex items-center space-x-6 mt-1 p-3 rounded-lg bg-[#0f1616] border border-transparent focus-within:border-teal-600 transition-colors">
                    <label class="flex items-center cursor-pointer hover:text-teal-400 transition-colors">
                        <input type="radio" v-model="form.genero" value="feminino" class="form-radio">
                        <span class="ml-2 text-sm">Feminino</span>
                    </label>
                    <label class="flex items-center cursor-pointer hover:text-teal-400 transition-colors">
                        <input type="radio" v-model="form.genero" value="masculino" class="form-radio">
                        <span class="ml-2 text-sm">Masculino</span>
                    </label>
                </div>
            </div>
        </div>

      <div class="pt-4 border-t border-gray-700 flex justify-end">
        <button type="submit" :disabled="saving" class="bg-teal-700 hover:bg-teal-600 text-white font-semibold py-3 px-8 rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-teal-900/20">
          {{ saving ? 'Salvando...' : 'Salvar Alterações' }}
        </button>
      </div>
    </form>
  </div>
  <div v-else class="text-center text-red-400 mt-10">Cliente não encontrado.</div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const props = defineProps({
  id: {
    type: [String, Number],
    required: true
  }
});

const router = useRouter();
const loading = ref(true);
const saving = ref(false);
const cliente = ref(null);
const errorMessage = ref('');

const form = reactive({
  nome: '',
  email: '',
  celular: '',
  data_nascimento: '',
  profissao: '',
  lateralidade: '',
  genero: '',
  senha: '',
  confirmarSenha: '' // Novo campo
});

const formatarCelular = (event) => {
  let value = event.target.value.replace(/\D/g, '');
  if (value.length > 11) value = value.slice(0, 11);
  value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
  value = value.replace(/(\d)(\d{4})$/, '$1-$2');
  form.celular = value;
};

const fetchCliente = async () => {
  try {
    const response = await axios.get(`/api/usuarios/${props.id}`);
    cliente.value = response.data.data || response.data;

    form.nome = cliente.value.nome;
    form.email = cliente.value.email;
    form.celular = cliente.value.celular;
    form.data_nascimento = cliente.value.data_nascimento;
    form.profissao = cliente.value.profissao;
    form.lateralidade = cliente.value.lateralidade;
    form.genero = cliente.value.genero;

  } catch (error) {
    console.error("Erro ao buscar dados do cliente:", error);
    errorMessage.value = "Erro ao carregar os dados.";
  } finally {
    loading.value = false;
  }
};

const submitForm = async () => {
  saving.value = true;
  errorMessage.value = '';

  // 1. Validação de Senha (Frontend)
  if (form.senha) {
      if (form.senha.length < 8) {
          errorMessage.value = "A nova senha deve ter no mínimo 8 caracteres.";
          saving.value = false;
          return;
      }
      if (form.senha !== form.confirmarSenha) {
          errorMessage.value = "A confirmação da senha não confere.";
          saving.value = false;
          return;
      }
  }

  // 2. Prepara Payload
  const payload = {
      ...form,
      celular: form.celular ? form.celular.replace(/\D/g, '') : null,
  };

  // Limpeza: Remove a senha se vazia OU remove a confirmação (não vai pro back)
  delete payload.confirmarSenha; // Nunca enviar confirmação para API se não for usar regra 'confirmed' do Laravel
  if (!payload.senha) {
      delete payload.senha;
  }

  try {
    await axios.put(`/api/usuarios/${props.id}`, payload);
    router.push({ name: 'detalhes-cliente', params: { id: props.id } });
  } catch (error) {
    console.error("Erro ao atualizar cliente:", error);
    if (error.response && error.response.data && error.response.data.errors) {
        const firstErrorKey = Object.keys(error.response.data.errors)[0];
        errorMessage.value = error.response.data.errors[firstErrorKey][0];
    } else {
        errorMessage.value = "Ocorreu um erro ao salvar as alterações.";
    }
  } finally {
    saving.value = false;
  }
};

onMounted(fetchCliente);
</script>

<style scoped>
.form-input {
  @apply w-full p-3 rounded-lg bg-[#0f1616] text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-600 border border-transparent transition-all;
}
.form-radio {
  @apply w-5 h-5 text-teal-600 bg-gray-700 border-gray-600 focus:ring-teal-500 focus:ring-2 cursor-pointer;
}
</style>
