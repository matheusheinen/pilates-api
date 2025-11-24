<template>
  <div class="bg-[#151515] p-6 rounded-xl text-white">

    <h3 class="text-lg font-semibold">Gerenciar Planos</h3>
    <p class="text-[#a0a0a0] mb-6">Defina os pacotes de aulas e valores disponíveis.</p>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

      <div class="lg:col-span-1 bg-[#242424] p-6 rounded-xl self-start border border-gray-700 shadow-md">
        <form @submit.prevent="salvarPlano" class="space-y-4">

          <h4 class="font-semibold text-white border-b border-gray-600 pb-2 mb-4">
              {{ modoEdicao ? 'Editar Plano' : 'Novo Plano' }}
          </h4>

          <div v-if="mensagemErro" class="p-3 bg-red-900/50 border border-red-800 text-red-200 rounded text-sm">
             {{ mensagemErro }}
          </div>

          <div>
            <label class="block text-xs text-gray-400 mb-1 ml-1">Nome do Plano</label>
            <input v-model="form.nome" type="text" placeholder="Ex: Mensal 2x" class="form-input-style" required>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs text-gray-400 mb-1 ml-1">Aulas/Semana</label>
                <input v-model="form.numero_aulas" type="number" min="1" class="form-input-style" required>
            </div>
            <div>
                <label class="block text-xs text-gray-400 mb-1 ml-1">Preço (R$)</label>
                <input v-model="form.preco" type="number" step="0.01" min="0" class="form-input-style" required>
            </div>
          </div>

          <div class="flex gap-2 pt-2">
              <button v-if="modoEdicao" type="button" @click="cancelarEdicao" class="flex-1 bg-gray-600 hover:bg-gray-500 text-white font-semibold py-2 rounded-lg transition-colors text-sm">
                Cancelar
              </button>
              <button type="submit" :disabled="salvando" class="flex-1 bg-teal-700 hover:bg-teal-600 text-white font-semibold py-2 rounded-lg transition-colors disabled:opacity-50">
                {{ salvando ? 'Salvando...' : (modoEdicao ? 'Atualizar' : 'Cadastrar') }}
              </button>
          </div>
        </form>
      </div>

      <div class="lg:col-span-2">
        <div class="bg-[#242424] rounded-xl border border-gray-700 overflow-hidden shadow-md">

            <div v-if="loading" class="p-8 text-center text-teal-500 animate-pulse">
                Carregando planos...
            </div>

            <div v-else-if="planos.length === 0" class="p-8 text-center text-gray-500">
                Nenhum plano cadastrado.
            </div>

            <div v-else class="divide-y divide-gray-700">
                <div class="grid grid-cols-12 gap-2 p-3 bg-[#2a2a2a] text-xs text-gray-400 font-medium uppercase tracking-wider">
                   <div class="col-span-5">Nome do Plano</div>
                   <div class="col-span-2 text-center">Aulas</div>
                   <div class="col-span-2 text-right">Preço</div>
                   <div class="col-span-3 text-right">Ações</div>
                </div>

                <div v-for="plano in planos" :key="plano.id" class="grid grid-cols-12 items-center gap-2 py-3 px-3 hover:bg-[#2b2b2b] transition duration-150 text-sm">

                  <div class="col-span-5 font-medium text-white">
                    {{ plano.nome }}
                  </div>

                  <div class="col-span-2 text-center text-gray-300">
                    {{ plano.numero_aulas }}x / sem
                  </div>

                  <div class="col-span-2 text-right font-mono text-teal-400">
                    R$ {{ formatarPreco(plano.preco) }}
                  </div>

                  <div class="col-span-3 text-right flex justify-end gap-2">
                     <button
                        @click="iniciarEdicao(plano)"
                        class="text-blue-400 hover:text-blue-300 bg-blue-900/20 hover:bg-blue-900/40 px-2 py-1 rounded transition font-semibold text-xs flex items-center"
                        title="Editar Plano">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                        Editar
                     </button>

                     <button
                        @click="deletarPlano(plano.id)"
                        class="text-red-400 hover:text-red-300 bg-red-900/20 hover:bg-red-900/40 px-2 py-1 rounded transition font-semibold text-xs flex items-center"
                        title="Excluir Plano">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        Excluir
                     </button>
                  </div>
                </div>
            </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';

const planos = ref([]);
const loading = ref(true);
const modoEdicao = ref(false);
const salvando = ref(false);
const mensagemErro = ref('');

const form = reactive({
    id: null,
    nome: '',
    numero_aulas: 1,
    preco: ''
});

// 1. Carregar Dados
const carregarPlanos = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/planos');
        planos.value = response.data.data || response.data;
    } catch (error) {
        console.error("Erro ao carregar planos:", error);
        mensagemErro.value = "Não foi possível carregar a lista de planos.";
    } finally {
        loading.value = false;
    }
};

// 2. Lógica de Edição (Preenche o form lateral)
const iniciarEdicao = (plano) => {
    modoEdicao.value = true;
    mensagemErro.value = '';

    form.id = plano.id;
    form.nome = plano.nome;
    form.numero_aulas = plano.numero_aulas;
    form.preco = plano.preco;
};

// 3. Cancelar/Resetar
const cancelarEdicao = () => {
    modoEdicao.value = false;
    mensagemErro.value = '';

    form.id = null;
    form.nome = '';
    form.numero_aulas = 1;
    form.preco = '';
};

// 4. Salvar (Create ou Update)
const salvarPlano = async () => {
    salvando.value = true;
    mensagemErro.value = '';

    try {
        if (modoEdicao.value) {
            await axios.put(`/api/planos/${form.id}`, form);
            alert('Plano atualizado com sucesso!');
        } else {
            await axios.post('/api/planos', form);
            alert('Plano criado com sucesso!');
        }

        cancelarEdicao();
        await carregarPlanos();

    } catch (error) {
        console.error("Erro ao salvar:", error);
        if (error.response && error.response.data && error.response.data.message) {
            mensagemErro.value = error.response.data.message;
        } else {
            mensagemErro.value = "Ocorreu um erro ao salvar. Verifique os dados.";
        }
    } finally {
        salvando.value = false;
    }
};

// 5. Deletar
const deletarPlano = async (id) => {
    if (!confirm('Tem certeza que deseja excluir este plano?')) return;

    try {
        await axios.delete(`/api/planos/${id}`);
        await carregarPlanos();
        alert('Plano excluído com sucesso!');
    } catch (error) {
        console.error("Erro ao deletar:", error);
        alert("Erro ao excluir: Este plano pode estar vinculado a alunos.");
    }
};

// Helpers
const formatarPreco = (valor) => {
    return parseFloat(valor).toFixed(2).replace('.', ',');
};

onMounted(() => {
    carregarPlanos();
});
</script>

<style scoped>
.form-input-style {
    @apply w-full bg-[#1e1e1e] border border-[#444] text-white rounded p-2 text-sm focus:border-teal-600 focus:ring-1 focus:ring-teal-600 outline-none transition-all;
}
</style>
