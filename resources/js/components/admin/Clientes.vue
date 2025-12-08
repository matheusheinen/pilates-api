<template>
  <div class="bg-[#151515] p-6 rounded-xl text-white">
    <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-6 gap-4">
      <div>
        <h3 class="text-lg font-semibold">Gestão de Clientes</h3>
        <p class="text-[#a0a0a0]">Visualize e pesquise todos os alunos cadastrados no sistema.</p>
      </div>
      <div>
        <router-link
          :to="{ name: 'cadastro-cliente' }"
          class="w-full sm:w-auto flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-teal-700 hover:bg-teal-600 font-semibold transition-colors text-sm"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
          Cadastrar Cliente
        </router-link>
      </div>
    </div>

    <div class="mb-4">
      <input
        type="text"
        v-model="termoBusca"
        placeholder="Pesquisar por nome ou email..."
        class="form-input"
      >
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left">
        <thead class="border-b border-gray-700">
          <tr>
            <th class="p-3 text-sm font-semibold">Nome</th>
            <th class="p-3 text-sm font-semibold hidden sm:table-cell">Email</th>
            <th class="p-3 text-sm font-semibold hidden md:table-cell">Celular</th>
            <th class="p-3 text-sm font-semibold">Ações</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="4" class="text-center p-8 text-gray-400">Carregando alunos...</td>
          </tr>
          <tr v-else-if="clientesFiltrados.length === 0">
            <td colspan="4" class="text-center p-8 text-gray-400">Nenhum aluno encontrado.</td>
          </tr>
          <tr v-for="cliente in clientesFiltrados" :key="cliente.id" class="border-b border-gray-800 hover:bg-[#242424]">
            <td class="p-3 font-medium">{{ cliente.nome }}</td>
            <td class="p-3 text-gray-400 hidden sm:table-cell">{{ cliente.email }}</td>
            <td class="p-3 text-gray-400 hidden md:table-cell">{{ cliente.celular || 'Não informado' }}</td>
            <td class="p-3">
                <router-link
                    :to="{ name: 'detalhes-cliente', params: { id: cliente.id } }"
                    class="text-teal-500 hover:text-teal-400 text-sm font-semibold"
                >
                    Ver Detalhes
                </router-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import { useAlert } from '../../composables/useAlert';
const { mostrarErro } = useAlert();

const todosClientes = ref([]);
const termoBusca = ref('');
const loading = ref(true);

const fetchClientes = async () => {
  try {
    const response = await axios.get('/api/usuarios');
    // Filtramos para garantir que estamos a mostrar apenas utilizadores do tipo 'aluno'
    todosClientes.value = response.data.data.filter(user => user.tipo === 'aluno');
  } catch (error) {
    console.error("Erro ao buscar clientes:", error);
    mostrarErro("Não foi possível carregar a lista de clientes.");
  } finally {
    loading.value = false;
  }
};

const clientesFiltrados = computed(() => {
  if (!termoBusca.value) {
    return todosClientes.value;
  }
  const busca = termoBusca.value.toLowerCase();
  return todosClientes.value.filter(cliente =>
    cliente.nome.toLowerCase().includes(busca) ||
    cliente.email.toLowerCase().includes(busca)
  );
});

onMounted(fetchClientes);
</script>

<style>
/* Estilos permanecem os mesmos */
.form-input {
  @apply w-full p-2 rounded-lg bg-[#0f1616] text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-600;
}
</style>
