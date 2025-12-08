<template>
  <div class="space-y-6">
    <h2 class="text-xl font-bold text-white mb-4">Minhas Mensalidades</h2>

    <div v-if="loading" class="py-8 text-center text-gray-500 animate-pulse">Carregando financeiro...</div>

    <div v-else-if="mensalidades.length === 0" class="text-center py-10 bg-[#1a1a1a] rounded-xl border border-gray-800">
        <p class="text-gray-400">Nenhuma mensalidade encontrada.</p>
    </div>

    <div v-else class="space-y-4">
        <div v-for="fatura in mensalidades" :key="fatura.id" class="bg-[#1a1a1a] rounded-xl border border-gray-800 overflow-hidden">
            <div class="p-4 flex justify-between items-start">
                <div>
                    <p class="text-xs text-gray-500 uppercase font-bold mb-1">Vencimento</p>
                    <p class="text-lg font-bold text-white">{{ formatarData(fatura.data_vencimento) }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 uppercase font-bold mb-1">Valor</p>
                    <p class="text-lg font-mono text-teal-400">R$ {{ formatarPreco(fatura.valor) }}</p>
                </div>
            </div>

            <div class="px-4 pb-4 pt-2 border-t border-gray-800 bg-[#202020] flex items-center justify-between">
                <span :class="statusClass(fatura.status)" class="text-xs font-bold px-3 py-1 rounded-full uppercase flex items-center gap-1">
                    <span v-if="fatura.status === 'paga'">✓</span>
                    {{ fatura.status.replace('_', ' ') }}
                </span>

                <div v-if="fatura.status === 'pendente' || fatura.status === 'atrasada'">
                    <input type="file" :id="'upload-'+fatura.id" class="hidden" accept="image/*,application/pdf" @change="(e) => enviarComprovante(e, fatura.id)">
                    <label :for="'upload-'+fatura.id" class="cursor-pointer bg-teal-700 hover:bg-teal-600 text-white text-xs font-bold px-3 py-1.5 rounded transition flex items-center gap-1">
                        <svg v-if="uploadingId === fatura.id" class="animate-spin h-3 w-3" viewBox="0 0 24 24"></svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                        <span>Enviar Comprovante</span>
                    </label>
                </div>

                <div v-else-if="fatura.status === 'em_analise'" class="text-xs text-blue-400 italic">
                    Aguardando conferência.
                </div>
            </div>
        </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { format, parseISO } from 'date-fns';
import { ptBR } from 'date-fns/locale';
import { useAlert } from '../../composables/useAlert';

const { mostrarSucesso, mostrarErro } = useAlert();


const mensalidades = ref([]);
const loading = ref(true);
const uploadingId = ref(null);

const fetchMensalidades = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/mensalidades');
        mensalidades.value = response.data.data;
    } catch (error) {
        console.error(error);
    } finally {
        loading.value = false;
    }
};

const enviarComprovante = async (event, id) => {
    const file = event.target.files[0];
    if (!file) return;

    uploadingId.value = id;
    const formData = new FormData();
    formData.append('comprovante', file);
    // Data do pagamento assume HOJE
    const hoje = new Date();
    const offset = hoje.getTimezoneOffset();
    const dataLocal = new Date(hoje.getTime() - (offset * 60 * 1000)).toISOString().split('T')[0];
    formData.append('data_pagamento', dataLocal);

    try {
        await axios.post(`/api/mensalidades/${id}/comprovante`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        mostrarSucesso("Comprovante enviado com sucesso!");
        fetchMensalidades();
    } catch (error) {
        mostrarErro("Erro ao enviar: " + (error.response?.data?.message || 'Tente novamente.'));
    } finally {
        uploadingId.value = null;
    }
};

const formatarPreco = (v) => parseFloat(v).toFixed(2).replace('.', ',');
const formatarData = (d) => format(parseISO(d), 'dd/MM/yyyy');
const statusClass = (s) => {
    const map = {
        'pendente': 'bg-yellow-500/10 text-yellow-500',
        'atrasada': 'bg-red-500/10 text-red-500',
        'em_analise': 'bg-blue-500/10 text-blue-400',
        'paga': 'bg-green-500/10 text-green-500',
    };
    return map[s] || 'bg-gray-700 text-gray-400';
};

onMounted(fetchMensalidades);
</script>
