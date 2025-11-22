<template>
  <div class="min-h-screen w-full bg-[#151515] text-white font-sans flex flex-col justify-center items-center p-4">

    <div class="bg-[#121212]/20 backdrop-blur-lg border border-white/30 p-8 rounded-3xl shadow-lg w-full max-w-2xl">
      <div class="text-center mb-8">
        <img :src="logoUrl" alt="Logo" class="w-40 mx-auto mb-4"/>
        <h3 class="text-3xl font-bold">Cadastro de Novo Aluno</h3>
        <p class="text-gray-400">Dados Pessoais e de Contato</p>
      </div>

      <form @submit.prevent="handleRegister" class="space-y-4">
        <div v-if="successMessage" class="bg-green-500/20 border border-green-700 text-green-300 text-sm p-3 rounded-lg">
          {{ successMessage }}
        </div>
        <div v-if="errorMessage" class="bg-red-500/20 border border-red-700 text-red-300 text-sm p-3 rounded-lg">
          {{ errorMessage }}
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <input v-model="formData.nome" type="text" placeholder="Nome Completo *" required class="form-input">
          <input v-model="formData.email" type="email" placeholder="Email *" required class="form-input">
          <input v-model="formData.senha" type="password" placeholder="Senha *" required class="form-input">
          <input v-model="formData.celular" type="text" placeholder="Celular *" required @input="formatarCelular" maxlength="15" class="form-input">
          <input v-model="formData.cpf" @input="formatarCPF" maxlength="14" type="text" placeholder="CPF" class="form-input">
          <input v-model="formData.profissao" type="text" placeholder="Profissão" class="form-input">
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-xs text-gray-400 ml-1">Lateralidade *</label>
                <div class="flex items-center space-x-6 mt-1 p-3 rounded-lg bg-[#0f1616] border border-transparent focus-within:border-teal-600">
                <label class="flex items-center cursor-pointer">
                    <input type="radio" v-model="formData.lateralidade" value="destro" name="lateralidade" class="form-radio">
                    <span class="ml-2 text-sm">Destro</span>
                </label>
                <label class="flex items-center cursor-pointer">
                    <input type="radio" v-model="formData.lateralidade" value="canhoto" name="lateralidade" class="form-radio">
                    <span class="ml-2 text-sm">Canhoto</span>
                </label>
                </div>
            </div>

            <div>
                <label class="text-xs text-gray-400 ml-1">Gênero *</label>
                <div class="flex items-center space-x-6 mt-1 p-3 rounded-lg bg-[#0f1616]">
                    <label class="flex items-center cursor-pointer"><input type="radio" v-model="formData.genero" value="feminino" name="genero" class="form-radio"><span class="ml-2 text-sm">Feminino</span></label>
                    <label class="flex items-center cursor-pointer"><input type="radio" v-model="formData.genero" value="masculino" name="genero" class="form-radio"><span class="ml-2 text-sm">Masculino</span></label>
                </div>
            </div>
        </div>

        <div class="w-full">
            <label class="text-xs text-gray-400 ml-1">Data de Nascimento</label>
            <input v-model="formData.data_nascimento" type="date" class="form-input">
        </div>

        <button type="submit" :disabled="loading" class="w-full py-3 mt-4 rounded-lg bg-teal-700 hover:bg-teal-600 text-white font-semibold transition-colors disabled:opacity-50">
          {{ loading ? 'Cadastrando...' : 'Finalizar Cadastro' }}
        </button>
      </form>

      <div class="text-center mt-6">
        <p class="text-gray-400">Já tem uma conta? <router-link to="/login" class="font-semibold text-teal-500 hover:text-teal-400">Faça o login</router-link></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import logoUrl from '../../assets/logo.png';

const router = useRouter();
const loading = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

const formData = reactive({
  nome: '',
  email: '',
  senha: '',
  celular: '',
  cpf: '',
  data_nascimento: '',
  genero: '',
  profissao: '',
  tipo: 'aluno',
  lateralidade: ''
});

// --- NOVA FUNÇÃO: MÁSCARA DE CELULAR ---
const formatarCelular = (event) => {
  let value = event.target.value.replace(/\D/g, ''); // Remove tudo que não é dígito

  if (value.length > 11) value = value.slice(0, 11); // Limita a 11 números

  // Máscara (XX) XXXXX-XXXX
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

const validarCPF = (cpf) => {
    const cpfLimpo = cpf.replace(/[^\d]+/g, '');
    return cpfLimpo.length === 11;
};

const handleRegister = async () => {
  loading.value = true;
  successMessage.value = '';
  errorMessage.value = '';

  // Validações visuais (Front-end)
  if (formData.cpf && !validarCPF(formData.cpf)) {
      errorMessage.value = 'CPF inválido.';
      loading.value = false;
      return;
  }

  if (!formData.lateralidade) {
      errorMessage.value = 'Por favor, selecione a lateralidade (Destro ou Canhoto).';
      loading.value = false;
      return;
  }

  if (!formData.genero) {
      errorMessage.value = 'Por favor, selecione o gênero.';
      loading.value = false;
      return;
  }

  // --- CORREÇÃO AQUI ---
  // Prepara os dados para envio: remove a máscara (pontos, traços, parenteses)
  // Mantemos o formData intacto para não "quebrar" a visualização na tela enquanto carrega
  const payload = {
      ...formData,
      cpf: formData.cpf.replace(/\D/g, ''),       // Remove pontos e traço
      celular: formData.celular.replace(/\D/g, '') // Remove ( ) e -
  };

  try {
    // Envia o 'payload' limpo, em vez do 'formData' sujo
    const response = await axios.post('/api/register', payload);

    successMessage.value = 'Cadastro realizado com sucesso! Redirecionando...';

    // Limpa o formulário original
    Object.keys(formData).forEach(key => formData[key] = '');
    formData.tipo = 'aluno';

    setTimeout(() => {
      router.push('/login');
    }, 2000);

  } catch (error) {
    if (error.response && error.response.data) {
        if (error.response.data.errors) {
             const firstErrorKey = Object.keys(error.response.data.errors)[0];
             errorMessage.value = error.response.data.errors[firstErrorKey][0];
        } else if (error.response.data.message) {
             errorMessage.value = error.response.data.message;
        }
    } else {
        errorMessage.value = 'Erro ao realizar cadastro. Tente novamente.';
    }
    console.error(error);
  } finally {
    loading.value = false;
  }
};

</script>

<style scoped>
.form-input {
  @apply w-full p-3 rounded-lg bg-[#0f1616] text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-600;
}
.form-radio {
  @apply w-5 h-5 text-teal-600 bg-gray-700 border-gray-600 focus:ring-teal-500 focus:ring-2;
}
</style>
