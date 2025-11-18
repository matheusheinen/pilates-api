<template>
    <div class="w-full">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-white">Gerenciar Planos</h1>
            <button
                @click="abrirModal()"
                class="bg-[#009088] hover:bg-[#007972] text-white px-4 py-2 rounded-lg shadow transition duration-200 flex items-center gap-2">
                <span>+</span> Novo Plano
            </button>
        </div>

        <div class="bg-[#1e1e1e] rounded-xl shadow overflow-hidden border border-[#333]">
            <table class="min-w-full divide-y divide-[#333]">
                <thead class="bg-[#242424]">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Nome</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Aulas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Preço</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-[#1e1e1e] divide-y divide-[#333]">
                    <tr v-if="loading">
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">Carregando planos...</td>
                    </tr>
                    <tr v-else v-for="plano in planos" :key="plano.id" class="hover:bg-[#2b2b2b] transition duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-100 font-medium">{{ plano.nome }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-400">{{ plano.numero_aulas }}x na semana</td>
                        <td class="px-6 py-4 whitespace-nowrap text-[#009088] font-bold">R$ {{ formatarPreco(plano.preco) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button @click="abrirModal(plano)" class="text-blue-400 hover:text-blue-300 mr-4 transition-colors">Editar</button>
                            <button @click="deletarPlano(plano.id)" class="text-red-400 hover:text-red-300 transition-colors">Excluir</button>
                        </td>
                    </tr>
                    <tr v-if="!loading && planos.length === 0">
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">Nenhum plano cadastrado.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="mostrarModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">

                <div class="fixed inset-0 bg-black bg-opacity-80 transition-opacity" @click="fecharModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-[#1e1e1e] rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-[#333]">
                    <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-bold text-white mb-4" id="modal-title">
                            {{ modoEdicao ? 'Editar Plano' : 'Novo Plano' }}
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-400">Nome do Plano</label>
                                <input v-model="form.nome" type="text" placeholder="Ex: Mensal 2x" class="mt-1 block w-full bg-[#2b2b2b] border border-[#444] text-white rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-[#009088] focus:border-[#009088] sm:text-sm placeholder-gray-600">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-400">Nº de Aulas (Semanal)</label>
                                    <input v-model.number="form.numero_aulas" type="number" min="1" class="mt-1 block w-full bg-[#2b2b2b] border border-[#444] text-white rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-[#009088] focus:border-[#009088] sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-400">Preço (R$)</label>
                                    <input v-model="form.preco" type="number" step="0.01" class="mt-1 block w-full bg-[#2b2b2b] border border-[#444] text-white rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-[#009088] focus:border-[#009088] sm:text-sm">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-[#242424] px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-[#333]">
                        <button
                            @click="salvarPlano"
                            type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-[#009088] text-base font-medium text-white hover:bg-[#007972] focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-colors"
                            :disabled="salvando">
                            {{ salvando ? 'Salvando...' : 'Salvar' }}
                        </button>
                        <button
                            @click="fecharModal"
                            type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-[#444] shadow-sm px-4 py-2 bg-[#2b2b2b] text-base font-medium text-gray-300 hover:bg-[#333] focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue';
import axios from 'axios';

const planos = ref([]);
const loading = ref(true);
const mostrarModal = ref(false);
const modoEdicao = ref(false);
const salvando = ref(false);

const form = reactive({
    id: null,
    nome: '',
    numero_aulas: 1,
    preco: ''
});

onMounted(async () => {
    await carregarPlanos();
});

const carregarPlanos = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/planos');
        planos.value = response.data;
    } catch (error) {
        console.error("Erro ao carregar planos:", error);
        alert("Não foi possível carregar os planos.");
    } finally {
        loading.value = false;
    }
};

const formatarPreco = (valor) => {
    return parseFloat(valor).toFixed(2).replace('.', ',');
};

const abrirModal = (plano = null) => {
    if (plano) {
        modoEdicao.value = true;
        form.id = plano.id;
        form.nome = plano.nome;
        form.numero_aulas = plano.numero_aulas;
        form.preco = plano.preco;
    } else {
        modoEdicao.value = false;
        resetForm();
    }
    mostrarModal.value = true;
};

const fecharModal = () => {
    mostrarModal.value = false;
    resetForm();
};

const resetForm = () => {
    form.id = null;
    form.nome = '';
    form.numero_aulas = 1;
    form.preco = '';
};

const salvarPlano = async () => {
    salvando.value = true;
    try {
        if (modoEdicao.value) {
            await axios.put(`/api/planos/${form.id}`, form);
            alert('Plano atualizado com sucesso!');
        } else {
            await axios.post('/api/planos', form);
            alert('Plano criado com sucesso!');
        }
        fecharModal();
        await carregarPlanos();
    } catch (error) {
        console.error("Erro ao salvar:", error);
        alert("Ocorreu um erro ao salvar o plano. Verifique os dados.");
    } finally {
        salvando.value = false;
    }
};

const deletarPlano = async (id) => {
    if (!confirm('Tem certeza que deseja excluir este plano?')) return;

    try {
        await axios.delete(`/api/planos/${id}`);
        alert('Plano excluído.');
        await carregarPlanos();
    } catch (error) {
        console.error("Erro ao excluir:", error);
        alert("Erro ao excluir o plano.");
    }
};
</script>
