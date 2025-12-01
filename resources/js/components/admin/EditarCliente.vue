<template>
  <div class="bg-[#151515] p-6 rounded-xl text-white">

    <div v-if="loading" class="flex flex-col items-center justify-center py-20 text-gray-500">
        <svg class="animate-spin h-10 w-10 mb-4 text-teal-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        <span class="text-base font-medium">Carregando dados do aluno...</span>
    </div>

    <div v-else-if="cliente">
      <div class="flex justify-between items-center mb-6">
        <div>
          <h3 class="text-lg font-semibold">Editar Cliente</h3>
          <p class="text-[#a0a0a0]">Atualize os dados de <span class="text-teal-400 font-medium">{{ cliente.nome }}</span>.</p>
        </div>
        <router-link
          :to="{ name: 'detalhes-cliente', params: { id: props.id } }"
          class="text-gray-400 hover:text-white transition-colors flex items-center gap-2 text-sm"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
          Voltar
        </router-link>
      </div>

      <div class="bg-[#121212]/20 backdrop-blur-lg border border-white/10 p-8 rounded-3xl shadow-lg w-full max-w-4xl mx-auto">

        <form @submit.prevent="submitForm" class="space-y-6">

          <div v-if="errorMessage" class="bg-red-500/20 border border-red-700 text-red-300 text-sm p-3 rounded-lg flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ errorMessage }}
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <label class="block text-sm text-gray-400 mb-1 ml-1">Nome Completo</label>
              <input v-model="form.nome" type="text" required class="form-input">
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm text-gray-400 mb-1 ml-1">Email</label>
              <input v-model="form.email" type="email" required class="form-input">
            </div>

            <div>
              <label class="block text-sm text-gray-400 mb-1 ml-1">CPF</label>
              <input
                v-model="form.cpf"
                type="text"
                v-mask="'###.###.###-##'"
                class="form-input opacity-75 cursor-not-allowed"
                disabled
                title="O CPF não pode ser alterado"
              >
            </div>

            <div>
              <label class="block text-sm text-gray-400 mb-1 ml-1">Celular</label>
              <input
                v-model="form.celular"
                type="text"
                v-mask="['(##) ####-####', '(##) #####-####']"
                placeholder="(00) 00000-0000"
                class="form-input"
              >
            </div>

            <div>
              <label class="block text-sm text-gray-400 mb-1 ml-1">Data de Nascimento</label>
              <input v-model="form.data_nascimento" type="date" class="form-input">
            </div>

            <div>
              <label class="block text-sm text-gray-400 mb-1 ml-1">Gênero</label>
              <select v-model="form.genero" class="form-input appearance-none cursor-pointer">
                  <option value="" disabled selected>Selecione</option>
                  <option value="Feminino">Feminino</option>
                  <option value="Masculino">Masculino</option>
                  <option value="Outro">Outro</option>
              </select>
            </div>

            <div>
              <label class="block text-sm text-gray-400 mb-1 ml-1">Profissão</label>
              <input v-model="form.profissao" type="text" placeholder="Ex: Advogada" class="form-input">
            </div>

            <div>
              <label class="block text-sm text-gray-400 mb-1 ml-1">Lateridade</label>
              <select v-model="form.lateridade" class="form-input appearance-none cursor-pointer">
                  <option value="" disabled selected>Selecione</option>
                  <option value="Destro">Destro</option>
                  <option value="Canhoto">Canhoto</option>
                  <option value="Ambidestro">Ambidestro</option>
              </select>
            </div>

            <div class="md:col-span-2 border-t border-gray-700/50 my-2 pt-4">
                <p class="text-xs text-gray-500 mb-4 uppercase font-bold tracking-wider">Alterar Senha (Opcional)</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                    <label class="block text-sm text-gray-400 mb-1 ml-1">Nova Senha</label>
                    <input v-model="form.senha" type="password" placeholder="Deixe em branco para manter" class="form-input">
                    </div>

                    <div>
                    <label class="block text-sm text-gray-400 mb-1 ml-1">Confirmar Nova Senha</label>
                    <input v-model="form.confirmarSenha" type="password" placeholder="Repita a nova senha" class="form-input">
                    </div>
                </div>
            </div>
          </div>

          <button
            type="submit"
            :disabled="saving"
            class="w-full bg-teal-600 hover:bg-teal-500 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-teal-900/20 hover:shadow-teal-900/40 transform hover:-translate-y-0.5 mt-4 flex justify-center items-center"
          >
            <svg v-if="saving" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            {{ saving ? 'Salvando Alterações...' : 'Salvar Alterações' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const props = defineProps(['id']);

const cliente = ref(null);
const loading = ref(true);
const saving = ref(false);
const errorMessage = ref('');

const form = reactive({
  nome: '',
  email: '',
  cpf: '',
  celular: '',
  data_nascimento: '',
  genero: '',       // Novo campo
  profissao: '',    // Novo campo
  lateridade: '',   // Novo campo
  senha: '',
  confirmarSenha: ''
});

const fetchCliente = async () => {
  loading.value = true;
  try {
    const response = await axios.get(`/api/usuarios/${props.id}`);
    cliente.value = response.data;

    // Preenche o formulário
    form.nome = cliente.value.nome;
    form.email = cliente.value.email;
    form.cpf = cliente.value.cpf;
    form.celular = cliente.value.celular;
    form.data_nascimento = cliente.value.data_nascimento;
    // Mapeando novos campos
    form.genero = cliente.value.genero || '';
    form.profissao = cliente.value.profissao || '';
    form.lateridade = cliente.value.lateridade || '';

  } catch (error) {
    console.error("Erro ao buscar cliente:", error);
    errorMessage.value = "Erro ao carregar os dados do cliente.";
  } finally {
    loading.value = false;
  }
};

const submitForm = async () => {
  saving.value = true;
  errorMessage.value = '';

  if (form.senha) {
      if (form.senha !== form.confirmarSenha) {
          errorMessage.value = "A confirmação da senha não confere.";
          saving.value = false;
          return;
      }
  }

  const payload = {
      ...form,
      celular: form.celular ? form.celular.replace(/\D/g, '') : null,
  };

  delete payload.cpf;
  delete payload.confirmarSenha;
  if (!payload.senha) {
      delete payload.senha;
  }

  try {
    await axios.put(`/api/usuarios/${props.id}`, payload);
    router.push({ name: 'detalhes-cliente', params: { id: props.id } });
  } catch (error) {
    console.error("Erro ao atualizar cliente:", error);
    if (error.response && error.response.data) {
         if(error.response.data.errors) {
            const firstErrorKey = Object.keys(error.response.data.errors)[0];
            errorMessage.value = error.response.data.errors[firstErrorKey][0];
         } else {
            errorMessage.value = error.response.data.message || "Ocorreu um erro ao salvar.";
         }
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
  @apply w-full p-3 rounded-lg bg-[#0a0a0a]/50 border border-gray-700 text-white placeholder-gray-500 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all outline-none;
}
</style>
