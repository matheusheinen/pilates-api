<template>
  <div class="flex flex-col md:flex-row h-screen w-screen relative bg-[#151515]">
    <img src="/public/logo.png" alt="Logo" class="absolute top-4 left-14 w-48 z-20 logo" style="transition: transform 0.3s ease;"/>
    <div class="absolute top-20 left-1/2 w-72 h-72 bg-teal-900 rounded-full opacity-60 pointer-events-none"></div>
    <div class="absolute bottom-10 right-10 w-64 h-64 bg-teal-800 rounded-full opacity-50 pointer-events-none"></div>

    <div class="flex flex-col md:flex-row h-full w-full">
      <div class="w-full md:w-1/2 flex flex-col justify-center px-8 md:px-16 text-white">
        <h2 class="text-6xl lg:text-8xl font-semibold">Entrar no<br>sistema</h2>
      </div>

      <div class="w-full md:w-1/2 flex justify-center items-center px-4 md:px-0">
        <div class="bg-[#121212]/20 backdrop-blur-lg border border-white/30 p-10 rounded-3xl shadow-lg w-full max-w-md relative z-10">
          <h3 class="text-3xl font-bold text-white mb-2">Login</h3>
          <p class="text-gray-400 mb-6">Entre com suas credenciais</p>

          <form @submit.prevent="handleLogin">
            <div class="mb-4">
              <input v-model="form.email" type="email" placeholder="Email" required class="w-full p-3 rounded-lg bg-[#0f1616] text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-600 transition-colors">
            </div>
            <div class="mb-4 relative">
              <input v-model="form.senha" :type="passwordFieldType" placeholder="Senha" required class="w-full p-3 rounded-lg bg-[#0f1616] text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-600 pr-10 transition-colors">
              <button type="button" @click="togglePassword" class="absolute right-3 top-3 text-gray-400 hover:text-white transition-colors">
                <ion-icon :name="passwordFieldType === 'password' ? 'eye-off' : 'eye'" class="h-6 w-6"></ion-icon>
              </button>
            </div>

            <div v-if="errorMessage" class="bg-red-900/50 border border-red-700 text-red-300 text-sm p-3 rounded-lg mb-4">
              {{ errorMessage }}
            </div>

            <button type="submit" class="w-full py-3 rounded-lg bg-teal-700 hover:bg-teal-600 text-white font-semibold mb-4 transition-colors shadow-md hover:shadow-lg">
              Login
            </button>
          </form>

          <p class="text-center text-gray-400 cursor-pointer hover:text-white transition-colors">Esqueceu a senha?</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const router = useRouter();

// Reatividade para os campos do formulário
const form = reactive({
    // IMPORTANTE: O backend espera 'email', não 'telefone', para o login.
    // Alterei para corresponder à sua API.
    email: '',
    senha: ''
});

// Reatividade para a mensagem de erro
const errorMessage = ref('');
// Reatividade para o tipo do campo de senha (password/text)
const showPassword = ref(false);

// Propriedade computada que retorna o tipo do input de senha
const passwordFieldType = computed(() => showPassword.value ? 'text' : 'password');

// Função para alternar a visibilidade da senha
const togglePassword = () => {
    showPassword.value = !showPassword.value;
};

// Função chamada ao submeter o formulário
const handleLogin = async () => {
    errorMessage.value = '';
    try {
        const response = await axios.post('/api/login', form);

        localStorage.setItem('authToken', response.data.access_token);
        localStorage.setItem('userData', JSON.stringify(response.data.usuario));

        axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.access_token}`;

        // Redireciona para a rota 'dashboard' (vamos criar em breve)
        // router.push({ name: 'dashboard' });

        alert(`Login bem-sucedido! Bem-vindo(a), ${response.data.usuario.nome}!`);

    } catch (error) {
        if (error.response && error.response.status === 401) {
            errorMessage.value = 'Email ou senha inválidos.';
        } else {
            errorMessage.value = 'Ocorreu um erro. Tente novamente mais tarde.';
        }
        console.error("Erro no login:", error);
    }
};
</script>

<style>
.logo:hover {
  transform: scale(1.05);
}
</style>
