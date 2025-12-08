<template>
  <div class="space-y-6">
    <h2 class="text-xl font-bold text-white mb-4">Meu Perfil</h2>

    <div class="bg-[#1a1a1a] p-6 rounded-xl border border-gray-800 text-center mb-6">
        <div class="w-20 h-20 bg-teal-700 rounded-full mx-auto flex items-center justify-center text-3xl font-bold text-white mb-3">
            {{ iniciais }}
        </div>
        <h3 class="text-lg font-bold text-white">{{ form.nome }}</h3>
        <p class="text-sm text-gray-500">{{ form.email }}</p>
    </div>

    <form @submit.prevent="salvar" class="bg-[#1a1a1a] p-6 rounded-xl border border-gray-800 space-y-4">
        <div>
            <label class="block text-xs text-gray-400 mb-1">Nome Completo</label>
            <input v-model="form.nome" type="text" class="form-input-mobile">
        </div>
        <div>
            <label class="block text-xs text-gray-400 mb-1">Celular / WhatsApp</label>
            <input v-model="form.celular" type="tel" class="form-input-mobile">
        </div>
        <div>
            <label class="block text-xs text-gray-400 mb-1">Profissão</label>
            <input v-model="form.profissao" type="text" class="form-input-mobile">
        </div>

        <div class="pt-4">
            <button type="submit" :disabled="salvando" class="w-full bg-teal-700 hover:bg-teal-600 text-white font-bold py-3 rounded-lg transition disabled:opacity-50">
                {{ salvando ? 'Salvando...' : 'Atualizar Dados' }}
            </button>
        </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import axios from 'axios';
import { useAlert } from '../../composables/useAlert';

const { mostrarErro, mostrarSucesso } = useAlert();

const salvando = ref(false);
const form = reactive({
    id: null,
    nome: '',
    email: '',
    celular: '',
    profissao: ''
});

const iniciais = computed(() => {
    if (!form.nome) return 'A';
    const parts = form.nome.split(' ');
    return (parts[0][0] + (parts[1]?.[0] || '')).toUpperCase();
});

const fetchDados = async () => {
    try {
        const user = JSON.parse(localStorage.getItem('userData'));
        // Busca dados frescos do banco
        const response = await axios.get(`/api/usuarios/${user.id}`);
        const data = response.data;

        form.id = data.id;
        form.nome = data.nome;
        form.email = data.email;
        form.celular = data.celular;
        form.profissao = data.profissao;
    } catch (error) {
        console.error(error);
    }
};

const salvar = async () => {
    salvando.value = true;
    try {
        await axios.put(`/api/usuarios/${form.id}`, form);
        mostrarSucesso('Dados atualizados com sucesso!');
        // Atualiza localStorage
        const user = JSON.parse(localStorage.getItem('userData'));
        user.nome = form.nome;
        localStorage.setItem('userData', JSON.stringify(user));
    } catch (error) {
        mostrarErro('Erro ao atualizar: ' + (error.response?.data?.message || 'Erro desconhecido.'));
    } finally {
        salvando.value = false;
    }
};

onMounted(fetchDados);
</script>

<style scoped>
.form-input-mobile {
    @apply w-full bg-[#0f1616] border border-gray-700 rounded-lg p-3 text-white focus:border-teal-500 focus:outline-none transition-colors;
}
</style>
