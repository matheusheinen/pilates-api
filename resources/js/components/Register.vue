<template>
  <div class="min-h-screen w-full bg-[#151515] text-white font-sans flex flex-col justify-center items-center p-4">

    <div class="bg-[#121212]/20 backdrop-blur-lg border border-white/30 p-8 rounded-3xl shadow-lg w-full max-w-2xl">
      <div class="text-center mb-8">
        <img :src="logoUrl" alt="Logo" class="w-40 mx-auto mb-4"/>
        <h3 class="text-3xl font-bold">Cadastro de Novo Aluno</h3>
        <p class="text-gray-400">Preencha os dados para começar</p>
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
          <input v-model="formData.celular" type="text" placeholder="Celular" class="form-input">
          <input v-model="formData.cpf" @input="formatarCPF" maxlength="14" type="text" placeholder="CPF" class="form-input">
          <input v-model="formData.profissao" type="text" placeholder="Profissão" class="form-input">

          <div class="md:col-span-1">
            <label class="text-xs text-gray-400 ml-1">Lateralidade</label>
            <div class="flex items-center space-x-6 mt-2 p-3 rounded-lg bg-[#0f1616] h-[50px]">
              <label class="flex items-center cursor-pointer">
                <input type="radio" v-model="formData.lateralidade" value="Destro" name="lateralidade" class="form-radio">
                <span class="ml-2 text-sm">Destro</span>
              </label>
              <label class="flex items-center cursor-pointer">
                <input type="radio" v-model="formData.lateralidade" value="Canhoto" name="lateralidade" class="form-radio">
                <span class="ml-2 text-sm">Canhoto</span>
              </label>
            </div>
          </div>

          <div class="md:col-span-1">
            <label class="text-xs text-gray-400 ml-1">Gênero</label>
            <div class="flex items-center space-x-6 mt-2 p-3 rounded-lg bg-[#0f1616]">
                <label class="flex items-center cursor-pointer"><input type="radio" v-model="formData.genero" value="feminino" name="genero" class="form-radio"><span class="ml-2 text-sm">Feminino</span></label>
                <label class="flex items-center cursor-pointer"><input type="radio" v-model="formData.genero" value="masculino" name="genero" class="form-radio"><span class="ml-2 text-sm">Masculino</span></label>
            </div>
        </div>

        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="w-full">
                <label class="text-xs text-gray-400 ml-1">Data de Nascimento</label>
                <input v-model="formData.data_nascimento" type="date" class="form-input">
            </div>
            <div class="w-full">
                <label class="text-xs text-gray-400 ml-1">Altura (m)</label>
                <input v-model="formData.altura" type="number" step="0.01" placeholder="Ex: 1.75" class="form-input">
            </div>
            <div class="w-full">
                <label class="text-xs text-gray-400 ml-1">Peso (kg)</label>
                <input v-model="formData.peso" type="number" step="0.1" placeholder="Ex: 70.5" class="form-input">
            </div>
        </div>

        <textarea v-model="formData.diagnostico_clinico" type="text" placeholder="Diagnóstico Clínico" class="form-input"></textarea>

        <textarea v-model="formData.queixa_principal" placeholder="Queixa Principal / Objetivos" rows="3" class="form-input"></textarea>

        <button type="submit" class="w-full py-3 mt-4 rounded-lg bg-teal-700 hover:bg-teal-600 text-white font-semibold transition-colors">
          Cadastrar
        </button>
      </form>

      <div class="text-center mt-6">
        <p class="text-gray-400">
          Já tem uma conta?
          <router-link to="/login" class="font-semibold text-teal-500 hover:text-teal-400">
            Faça o login
          </router-link>
        </p>
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

const formData = reactive({
  nome: '', email: '', senha: '', celular: '', cpf: '',
  data_nascimento: '', genero: '', profissao: '', altura: '',
  peso: '', queixa_principal: '', tipo: 'aluno',
  // Campos adicionados na lógica
  lateralidade: '',
  diagnostico_clinico: ''
});

const successMessage = ref('');
const errorMessage = ref('');

const formatarCPF = (event) => {
  let value = event.target.value.replace(/\D/g, '');
  value = value.replace(/(\d{3})(\d)/, '$1.$2');
  value = value.replace(/(\d{3})(\d)/, '$1.$2');
  value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
  formData.cpf = value;
};

const validarCPF = (cpf) => {
  if (!cpf) return true;
  cpf = cpf.replace(/[^\d]+/g, '');
  if (cpf === '' || cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) return false;
  let add = 0;
  for (let i = 0; i < 9; i++) add += parseInt(cpf.charAt(i)) * (10 - i);
  let rev = 11 - (add % 11);
  if (rev === 10 || rev === 11) rev = 0;
  if (rev !== parseInt(cpf.charAt(9))) return false;
  add = 0;
  for (let i = 0; i < 10; i++) add += parseInt(cpf.charAt(i)) * (11 - i);
  rev = 11 - (add % 11);
  if (rev === 10 || rev === 11) rev = 0;
  if (rev !== parseInt(cpf.charAt(10))) return false;
  return true;
};

const handleRegister = async () => {
  successMessage.value = '';
  errorMessage.value = '';

  if (formData.cpf && !validarCPF(formData.cpf)) {
    errorMessage.value = 'O CPF informado não é válido.';
    return;
  }

  try {
    const dadosParaEnviar = { ...formData, cpf: formData.cpf.replace(/\D/g, '') };

    await axios.post('/api/register', dadosParaEnviar);
    successMessage.value = 'Cadastro realizado com sucesso! A redirecionar para o login...';
    setTimeout(() => {
      router.push({ name: 'login' });
    }, 3000);
  } catch (error) {
    if (error.response && error.response.data.errors) {
      const validationErrors = error.response.data.errors;
      errorMessage.value = Object.values(validationErrors)[0][0];
    } else {
      errorMessage.value = 'Ocorreu um erro ao realizar o cadastro. Verifique os dados e tente novamente.';
    }
    console.error("Erro no cadastro:", error);
  }
};
</script>

<style>
/* Estilos permanecem os mesmos */
.form-input { @apply w-full p-3 rounded-lg bg-[#0f1616] text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-600; }
.form-radio { @apply w-4 h-4 text-teal-600 bg-gray-900 border-gray-700 focus:ring-teal-500 focus:ring-2; }
</style>
