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
            <input v-model="form.email" type="email" placeholder="Email" required class="w-full p-3 rounded-lg bg-[#0f1616] text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-600">
          </div>
          <div class="relative">
            <input v-model="form.senha" :type="passwordFieldType" placeholder="Senha" required class="w-full p-3 rounded-lg bg-[#0f1616] text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-600 pr-10">
            <button type="button" @click="togglePassword" class="absolute right-3 top-3 text-gray-400 hover:text-white">
              <ion-icon :name="passwordFieldType === 'password' ? 'eye-off' : 'eye'" class="h-6 w-6"></ion-icon>
            </button>
          </div>

          <div v-if="errorMessage" class="bg-red-900/50 border border-red-700 text-red-300 text-sm p-3 rounded-lg">
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
// O script permanece o mesmo
import { reactive, ref, computed } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import logoUrl from '../../assets/logo.png';

const router = useRouter();
const form = reactive({ email: '', senha: '' });
const errorMessage = ref('');
const showPassword = ref(false);
const passwordFieldType = computed(() => showPassword.value ? 'text' : 'password');
const togglePassword = () => { showPassword.value = !showPassword.value; };
const handleLogin = async () => {
    errorMessage.value = '';
    try {
        const response = await axios.post('/api/login', form);
        localStorage.setItem('authToken', response.data.access_token);
        localStorage.setItem('userData', JSON.stringify(response.data.usuario));
        axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.access_token}`;
        router.push({ name: 'dashboard' });
    } catch (error) {
        errorMessage.value = 'Email ou senha inválidos.';
        console.error("Erro no login:", error);
    }
};
</script>
