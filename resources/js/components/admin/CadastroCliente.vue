<template>
  <div class="bg-[#151515] min-h-screen text-white p-6">
    <div class="flex justify-between items-center mb-6 max-w-4xl mx-auto">
      <div>
        <h3 class="text-2xl font-bold">Cadastrar Novo Cliente</h3>
        <p class="text-gray-400 text-sm">Preencha os dados para registrar um aluno manualmente.</p>
      </div>
      <router-link
        to="/admin/clientes"
        class="text-gray-400 hover:text-white transition-colors flex items-center gap-2 text-sm bg-white/5 px-4 py-2 rounded-lg"
      >
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        Voltar
      </router-link>
    </div>

    <div class="bg-[#121212]/40 backdrop-blur-xl border border-white/10 p-8 rounded-3xl shadow-2xl w-full max-w-4xl mx-auto">

      <form @submit.prevent="cadastrarCliente" class="space-y-6">
        <div v-if="successMessage" class="bg-green-500/20 border border-green-500/50 text-green-300 text-sm p-4 rounded-xl flex items-center gap-3">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
          {{ successMessage }}
        </div>

        <div v-if="errorMessage" class="bg-red-500/20 border border-red-500/50 text-red-300 text-sm p-4 rounded-xl flex items-center gap-3">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          {{ errorMessage }}
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
          <div class="md:col-span-2">
             <label class="label-custom">Nome Completo</label>
             <input v-model="formData.nome" type="text" placeholder="Ex: Maria Silva" required class="form-input">
          </div>

          <div class="md:col-span-2">
             <label class="label-custom">Email</label>
             <input v-model="formData.email" type="email" placeholder="Ex: maria@email.com" required class="form-input">
          </div>

          <div>
            <label class="label-custom">CPF</label>
            <input
              v-model="formData.cpf"
              type="text"
              @input="formatarCPF"
              maxlength="14"
              placeholder="000.000.000-00"
              class="form-input"
            >
          </div>

          <div>
             <label class="label-custom">Celular</label>
             <input
              v-model="formData.celular"
              type="text"
              @input="formatarCelular"
              maxlength="15"
              placeholder="(00) 00000-0000"
              required
              class="form-input"
            >
          </div>

          <div>
             <label class="label-custom">Data de Nascimento</label>
             <input v-model="formData.data_nascimento" type="date" class="form-input text-gray-300">
          </div>

          <div>
             <label class="label-custom">Profissão</label>
             <input v-model="formData.profissao" type="text" placeholder="Ex: Advogada" class="form-input">
          </div>

          <div>
             <label class="label-custom">Gênero</label>
             <div class="relative">
               <select v-model="formData.genero" class="form-input appearance-none cursor-pointer">
                  <option value="" disabled selected>Selecione</option>
                  <option value="feminino">Feminino</option>
                  <option value="masculino">Masculino</option>
                  <option value="outro">Outro</option>
               </select>
               <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-400">
                 <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
               </div>
             </div>
          </div>

          <div>
             <label class="label-custom">Lateralidade</label>
             <div class="relative">
                <select v-model="formData.lateralidade" class="form-input appearance-none cursor-pointer">
                    <option value="" disabled selected>Selecione</option>
                    <option value="destro">Destro</option>
                    <option value="canhoto">Canhoto</option>
                    <option value="ambidestro">Ambidestro</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
             </div>
          </div>

          <div class="md:col-span-2 pt-4 border-t border-gray-700/50 mt-2">
             <p class="text-teal-400 text-xs font-bold uppercase tracking-widest mb-4">Credenciais de Acesso</p>
             <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="label-custom">Senha Provisória</label>
                    <input v-model="formData.senha" type="password" placeholder="********" required class="form-input">
                </div>
                <div>
                    <label class="label-custom">Confirmar Senha</label>
                    <input v-model="formData.confirmarSenha" type="password" placeholder="********" required class="form-input">
                </div>
             </div>
          </div>
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-gradient-to-r from-teal-600 to-teal-500 hover:from-teal-500 hover:to-teal-400 text-white font-bold py-4 rounded-xl transition-all shadow-lg shadow-teal-900/20 transform hover:-translate-y-0.5 mt-6 flex justify-center items-center gap-2"
        >
          <svg v-if="loading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
          <span>{{ loading ? 'Processando...' : 'Finalizar Cadastro' }}</span>
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

// CORREÇÃO 3: O objeto reativo agora usa os nomes exatos do banco de dados (Laravel)
const formData = reactive({
  nome: '',
  email: '',
  cpf: '',
  celular: '',
  data_nascimento: '',
  genero: '',
  profissao: '',
  lateralidade: '', // Antes era 'lateridade'
  senha: '',        // Antes era 'password'
  confirmarSenha: '',
  tipo: 'aluno'
});

const formatarCelular = (event) => {
  let value = event.target.value.replace(/\D/g, '');
  if (value.length > 11) value = value.slice(0, 11);
  value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
  value = value.replace(/(\d)(\d{4})$/, '$1-$2');
  formData.celular = value;
};

const formatarCPF = (event) => {
  let value = event.target.value.replace(/\D/g, '');
  if (value.length > 11) value = value.slice(0, 11);
  value = value.replace(/(\d{3})(\d)/, '$1.$2');
  value = value.replace(/(\d{3})(\d)/, '$1.$2');
  value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
  formData.cpf = value;
};

const cadastrarCliente = async () => {
  loading.value = true;
  errorMessage.value = '';
  successMessage.value = '';

  if (formData.senha !== formData.confirmarSenha) {
      errorMessage.value = 'As senhas não conferem.';
      loading.value = false;
      return;
  }

  // Prepara o payload removendo máscaras
  const payload = {
      ...formData,
      cpf: formData.cpf.replace(/\D/g, ''),
      celular: formData.celular.replace(/\D/g, '')
  };

  // Remove o campo de confirmação antes de enviar
  delete payload.confirmarSenha;

  try {
    await axios.post('/api/usuarios', payload);
    successMessage.value = 'Aluno cadastrado com sucesso!';

    // Reset seguro do formulário
    Object.keys(formData).forEach(key => {
        if (key !== 'tipo') formData[key] = '';
    });

    setTimeout(() => {
      router.push('/admin/clientes');
    }, 1500);

  } catch (error) {
    if (error.response?.data) {
        if (error.response.data.errors) {
            // Pega a primeira mensagem de erro disponível
            const firstError = Object.values(error.response.data.errors)[0][0];
            errorMessage.value = firstError;
        } else {
            errorMessage.value = error.response.data.message || 'Erro ao salvar.';
        }
    } else {
        errorMessage.value = 'Erro de conexão com o servidor.';
    }
    console.error("Erro API:", error.response?.data);
  } finally {
    loading.value = false;
  }
};
</script>

<style scoped>
.form-input {
  @apply w-full p-4 rounded-xl bg-[#0a0a0a]/60 border border-gray-700 text-white placeholder-gray-500 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all outline-none;
}
.label-custom {
  @apply block text-xs font-medium text-gray-400 mb-2 ml-1 uppercase tracking-wide;
}
</style>
