<template>
  <div class="flex md:flex-row min-h-screen w-full bg-[#151515] text-white font-sans">

    <div class="hidden md:flex md:w-1/2 flex-col justify-center items-center p-8 relative">
      <img :src="logoUrl" alt="Logo" class="absolute top-8 left-8 w-48 z-10"/>
      <h2 class="text-6xl lg:text-7xl font-semibold text-center">
        Entrar no<br>sistema
      </h2>
    </div>

    <div class="w-full md:w-1/2 flex flex-col justify-center items-center p-4">

      <img :src="logoUrl" alt="Logo" class="w-48 mb-8 md:hidden"/>

      <div class="bg-[#121212]/20 backdrop-blur-lg border border-white/30 p-8 rounded-3xl shadow-lg w-full max-w-md">
        <h3 class="text-3xl font-bold mb-2">Login</h3>
        <p class="text-gray-400 mb-6">Entre com suas credenciais</p>

        <form @submit.prevent="handleLogin" class="space-y-4">
          <div>
            <input v-model="form.login"
                   type="text"
                   placeholder="Email ou Celular"
                   required
                   class="w-full p-3 rounded-lg bg-[#0f1616] text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-600" />
          </div>
          <div>
            <div class="relative">
              <input v-model="form.senha"
                     :type="passwordFieldType"
                     placeholder="Senha"
                     required
                     class="w-full p-3 rounded-lg bg-[#0f1616] text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-600 pr-10" />
              <button type="button" @click="togglePassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-white">
                <svg v-if="showPassword" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.057 10.057 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.96 9.96 0 011.543-3.72M21 12c-1.275 4.057-5.065 7-9.543 7a9.96 9.96 0 01-3.72-1.543M12 12c1.933 0 3.5-1.567 3.5-3.5S13.933 5 12 5s-3.5 1.567-3.5 3.5S10.067 12 12 12z" /></svg>
                <svg v-else class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
              </button>
            </div>
          </div>

          <div v-if="errorMessage" class="bg-red-500/20 border border-red-700 text-red-300 text-sm p-3 rounded-lg">
            {{ errorMessage }}
          </div>

          <button type="submit" class="w-full py-3 rounded-lg bg-teal-700 hover:bg-teal-600 text-white font-semibold transition-colors shadow-md">
            Login
          </button>
        </form>

        <div class="text-center mt-6">
          <p class="text-gray-400">
            Não tem uma conta?
            <router-link to="/register" class="font-semibold text-teal-500 hover:text-teal-400">
              Cadastre-se
            </router-link>
          </p>
        </div>
        </div>

    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import logoUrl from '../../assets/logo.png';

const router = useRouter();
// CORREÇÃO: Renomeia 'email' para 'login'
const form = reactive({ login: '', senha: '' });
const errorMessage = ref('');
const showPassword = ref(false);
const passwordFieldType = computed(() => showPassword.value ? 'text' : 'password');
const togglePassword = () => { showPassword.value = !showPassword.value; };

const handleLogin = async () => {
    errorMessage.value = '';
    try {
        // A Axios envia 'login' e 'senha' para a API
        const response = await axios.post('/api/login', form);

        localStorage.setItem('authToken', response.data.access_token);
        localStorage.setItem('userData', JSON.stringify(response.data.usuario));
        axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.access_token}`;
        router.push({ name: 'dashboard-agenda' });
    } catch (error) {
        // Se a API retornar o erro 401 que configuramos, exibe a mensagem de falha
        errorMessage.value = 'Email, Celular ou senha inválidos.';
        console.error("Erro no Login:", error);
    }
};
</script>

<style scoped>
/* Estilos permanecem os mesmos */
</style>
