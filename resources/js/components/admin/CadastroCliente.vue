<template>
  <div class="bg-[#151515] p-6 rounded-xl text-white">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h3 class="text-lg font-semibold">Cadastrar Novo Cliente</h3>
        <p class="text-[#a0a0a0]">Preencha os dados abaixo para registrar um novo aluno.</p>
      </div>
      <router-link
        to="/admin/clientes"
        class="text-gray-400 hover:text-white transition-colors flex items-center gap-2 text-sm"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        Voltar
      </router-link>
    </div>

    <div class="bg-[#121212]/20 backdrop-blur-lg border border-white/10 p-8 rounded-3xl shadow-lg w-full max-w-4xl mx-auto">

      <form @submit.prevent="cadastrarCliente" class="space-y-6">
        <div v-if="successMessage" class="bg-green-500/20 border border-green-700 text-green-300 text-sm p-3 rounded-lg flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
          {{ successMessage }}
        </div>
        <div v-if="errorMessage" class="bg-red-500/20 border border-red-700 text-red-300 text-sm p-3 rounded-lg flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          {{ errorMessage }}
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="md:col-span-2">
             <label class="block text-sm text-gray-400 mb-1 ml-1">Nome Completo</label>
             <input v-model="formData.nome" type="text" placeholder="Ex: Maria Silva" required class="form-input">
          </div>

          <div class="md:col-span-2">
             <label class="block text-sm text-gray-400 mb-1 ml-1">Email</label>
             <input v-model="formData.email" type="email" placeholder="Ex: maria@email.com" required class="form-input">
          </div>

          <div>
            <label class="block text-sm text-gray-400 mb-1 ml-1">CPF</label>
            <input
              v-model="formData.cpf"
              type="text"
              v-mask="'###.###.###-##'"
              placeholder="000.000.000-00"
              required
              class="form-input"
            >
          </div>

          <div>
             <label class="block text-sm text-gray-400 mb-1 ml-1">Celular</label>
             <input
              v-model="formData.celular"
              type="text"
              v-mask="['(##) ####-####', '(##) #####-####']"
              placeholder="(00) 00000-0000"
              required
              class="form-input"
            >
          </div>

          <div>
             <label class="block text-sm text-gray-400 mb-1 ml-1">Data de Nascimento</label>
             <input v-model="formData.data_nascimento" type="date" required class="form-input">
          </div>

          <div>
             <label class="block text-sm text-gray-400 mb-1 ml-1">Gênero</label>
             <select v-model="formData.genero" class="form-input appearance-none cursor-pointer">
                <option value="" disabled selected>Selecione</option>
                <option value="Feminino">Feminino</option>
                <option value="Masculino">Masculino</option>
                <option value="Outro">Outro</option>
             </select>
          </div>

          <div>
             <label class="block text-sm text-gray-400 mb-1 ml-1">Profissão</label>
             <input v-model="formData.profissao" type="text" placeholder="Ex: Advogada" class="form-input">
          </div>

          <div>
             <label class="block text-sm text-gray-400 mb-1 ml-1">Lateridade</label>
             <select v-model="formData.lateridade" class="form-input appearance-none cursor-pointer">
                <option value="" disabled selected>Selecione</option>
                <option value="Destro">Destro</option>
                <option value="Canhoto">Canhoto</option>
                <option value="Ambidestro">Ambidestro</option>
             </select>
          </div>

          <div>
             <label class="block text-sm text-gray-400 mb-1 ml-1">Senha Provisória</label>
             <input v-model="formData.password" type="password" placeholder="********" required class="form-input">
          </div>

          <div>
             <label class="block text-sm text-gray-400 mb-1 ml-1">Confirmar Senha</label>
             <input v-model="formData.password_confirmation" type="password" placeholder="********" required class="form-input">
          </div>
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-teal-600 hover:bg-teal-500 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-teal-900/20 hover:shadow-teal-900/40 transform hover:-translate-y-0.5 mt-4 flex justify-center items-center"
        >
          <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
          {{ loading ? 'Cadastrando...' : 'Cadastrar Aluno' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const loading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const formData = reactive({
  nome: '',
  email: '',
  cpf: '',
  celular: '',
  data_nascimento: '',
  genero: '',       // Novo campo
  profissao: '',    // Novo campo
  lateridade: '',   // Novo campo
  password: '',
  password_confirmation: '',
  tipo: 'aluno'
});

const cadastrarCliente = async () => {
  loading.value = true;
  errorMessage.value = '';
  successMessage.value = '';

  const payload = {
      ...formData,
      cpf: formData.cpf.replace(/\D/g, ''),
      celular: formData.celular.replace(/\D/g, '')
  };

  try {
    await axios.post('/api/usuarios', payload);

    successMessage.value = 'Aluno cadastrado com sucesso! Redirecionando...';

    Object.keys(formData).forEach(key => {
        if (key !== 'tipo') formData[key] = '';
    });

    setTimeout(() => {
      router.push('/admin/clientes');
    }, 1500);

  } catch (error) {
    if (error.response && error.response.data) {
        if (error.response.data.errors) {
             const firstErrorKey = Object.keys(error.response.data.errors)[0];
             errorMessage.value = error.response.data.errors[firstErrorKey][0];
        } else if (error.response.data.message) {
             errorMessage.value = error.response.data.message;
        }
    } else {
        errorMessage.value = 'Erro ao cadastrar. Verifique os dados.';
    }
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.form-input {
  @apply w-full p-3 rounded-lg bg-[#0a0a0a]/50 border border-gray-700 text-white placeholder-gray-500 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all outline-none;
}
</style>
