<template>
  <div class="min-h-screen w-full bg-[#151515] text-white font-sans flex flex-col justify-center items-center p-4">

    <div class="mb-8 flex gap-3">
        <div class="h-2 w-12 rounded-full transition-all duration-300" :class="step >= 1 ? 'bg-teal-500' : 'bg-gray-700'"></div>
        <div class="h-2 w-12 rounded-full transition-all duration-300" :class="step >= 2 ? 'bg-teal-500' : 'bg-gray-700'"></div>
    </div>

    <div class="bg-[#121212]/30 backdrop-blur-xl border border-white/10 p-8 rounded-3xl shadow-2xl w-full max-w-2xl relative overflow-hidden">

      <div class="text-center mb-8 relative z-10">
        <h3 class="text-3xl font-bold">{{ step === 1 ? 'Criar Conta' : 'Agendar 1ª Aula' }}</h3>
        <p class="text-gray-400 mt-2">
            {{ step === 1 ? 'Preencha seus dados para começar.' : 'Escolha o melhor horário para sua aula experimental.' }}
        </p>
      </div>

      <form @submit.prevent="avancarEtapa" class="space-y-4 relative z-10">

        <div v-if="errorMessage" class="bg-red-500/20 border border-red-500/50 text-red-200 text-sm p-3 rounded-lg text-center flex items-center justify-center gap-2">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          {{ errorMessage }}
        </div>

        <div v-show="step === 1" class="space-y-4 animate-fade-in">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input v-model="formData.nome" type="text" placeholder="Nome Completo *" required class="form-input">
                <input v-model="formData.email" type="email" placeholder="Email *" required class="form-input">
                <input v-model="formData.senha" type="password" placeholder="Senha *" required class="form-input">

                <input
                    v-model="formData.celular"
                    type="text"
                    placeholder="Celular *"
                    required
                    @input="formatarCelular"
                    maxlength="15"
                    class="form-input"
                >

                <input
                    v-model="formData.cpf"
                    @input="formatarCPF"
                    maxlength="14"
                    type="text"
                    placeholder="CPF"
                    class="form-input"
                >

                <input v-model="formData.profissao" type="text" placeholder="Profissão" class="form-input">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div class="bg-[#0f1616]/50 p-4 rounded-xl border border-gray-700/50">
                    <label class="text-xs text-gray-400 uppercase font-bold">Lateralidade *</label>
                    <div class="flex flex-wrap gap-4 mt-2">
                        <label class="flex items-center cursor-pointer hover:text-teal-400 transition-colors">
                            <input type="radio" v-model="formData.lateralidade" value="destro" class="form-radio text-teal-500">
                            <span class="ml-2 text-sm">Destro</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:text-teal-400 transition-colors">
                            <input type="radio" v-model="formData.lateralidade" value="canhoto" class="form-radio text-teal-500">
                            <span class="ml-2 text-sm">Canhoto</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:text-teal-400 transition-colors">
                            <input type="radio" v-model="formData.lateralidade" value="ambidestro" class="form-radio text-teal-500">
                            <span class="ml-2 text-sm">Ambidestro</span>
                        </label>
                    </div>
                </div>

                <div class="bg-[#0f1616]/50 p-4 rounded-xl border border-gray-700/50">
                    <label class="text-xs text-gray-400 uppercase font-bold">Gênero *</label>
                    <div class="flex flex-wrap gap-4 mt-2">
                        <label class="flex items-center cursor-pointer hover:text-teal-400 transition-colors">
                            <input type="radio" v-model="formData.genero" value="feminino" class="form-radio text-teal-500">
                            <span class="ml-2 text-sm">Feminino</span>
                        </label>
                        <label class="flex items-center cursor-pointer hover:text-teal-400 transition-colors">
                            <input type="radio" v-model="formData.genero" value="masculino" class="form-radio text-teal-500">
                            <span class="ml-2 text-sm">Masculino</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="w-full">
                <label class="text-xs text-gray-400 ml-1 mb-1 block">Data de Nascimento</label>
                <input v-model="formData.data_nascimento" type="date" class="form-input text-gray-300">
            </div>

            <button type="submit" class="btn-primary mt-6">
                Continuar para Agendamento
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
            </button>
        </div>

        <div v-show="step === 2" class="animate-fade-in space-y-4">

            <div v-if="loadingSlots" class="text-center py-10">
                <svg class="animate-spin h-8 w-8 text-teal-500 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                <p class="text-gray-400 mt-2 text-sm">Buscando horários disponíveis...</p>
            </div>

            <div v-else-if="horariosDisponiveis.length === 0" class="text-center py-8 bg-white/5 rounded-xl">
                <p class="text-gray-300">Nenhum horário disponível encontrado para os próximos 14 dias.</p>
                <button type="button" @click="pularAgendamento" class="text-teal-400 hover:text-teal-300 text-sm mt-2 underline">
                    Finalizar cadastro sem agendar
                </button>
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-80 overflow-y-auto pr-2 custom-scrollbar">
                <div
                    v-for="(slot, index) in horariosDisponiveis"
                    :key="index"
                    @click="selecionarHorario(slot)"
                    class="p-4 rounded-xl border cursor-pointer transition-all duration-200 relative group"
                    :class="aulaSelecionada === slot ? 'bg-teal-600/20 border-teal-500' : 'bg-[#0f1616] border-gray-700 hover:border-teal-500/50'"
                >
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-bold text-white capitalize">{{ formatarData(slot.data_hora) }}</p>
                            <p class="text-sm text-gray-300">{{ slot.horario }} - {{ slot.dia_semana }}</p>
                        </div>
                        <span class="text-xs bg-gray-800 px-2 py-1 rounded text-gray-400">
                            {{ slot.vagas_restantes }} vagas
                        </span>
                    </div>

                    <div v-if="aulaSelecionada === slot" class="absolute top-2 right-2 text-teal-400">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="button" @click="step = 1" class="px-6 py-3 rounded-xl bg-gray-700 text-white font-medium hover:bg-gray-600 transition-colors">
                    Voltar
                </button>
                <button
                    type="button"
                    @click="finalizarCadastro"
                    :disabled="loadingSubmit"
                    class="flex-1 btn-primary"
                    :class="{'opacity-50 cursor-not-allowed': loadingSubmit || (!aulaSelecionada && horariosDisponiveis.length > 0)}"
                >
                    {{ loadingSubmit ? 'Cadastrando...' : (aulaSelecionada ? 'Confirmar Agendamento' : 'Selecione um horário') }}
                </button>
            </div>

            <div class="text-center mt-2">
                 <button type="button" @click="pularAgendamento" class="text-xs text-gray-500 hover:text-gray-300 underline">
                    Pular etapa de agendamento
                </button>
            </div>
        </div>

      </form>

      <div class="text-center mt-6 relative z-10">
        <p class="text-gray-400 text-sm">Já tem uma conta? <router-link to="/login" class="font-semibold text-teal-500 hover:text-teal-400 transition-colors">Fazer login</router-link></p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

// Não precisamos de bibliotecas externas para data se usarmos Intl nativo
const router = useRouter();
const step = ref(1);
const loadingSlots = ref(false);
const loadingSubmit = ref(false);
const errorMessage = ref('');
const horariosDisponiveis = ref([]);
const aulaSelecionada = ref(null);

const formData = reactive({
  nome: '',
  email: '',
  senha: '', // Campo correto para o Laravel
  celular: '',
  cpf: '',
  data_nascimento: '',
  genero: '',
  profissao: '',
  lateralidade: '', // Campo correto para o Laravel
  tipo: 'aluno'
});

// --- MÁSCARAS ---
const formatarCelular = (event) => {
  let value = event.target.value.replace(/\D/g, '');
  if (value.length > 11) value = value.slice(0, 11);
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

// --- NAVEGAÇÃO ---
const avancarEtapa = async () => {
    // Validação Manual Etapa 1
    if (!formData.nome || !formData.email || !formData.senha || !formData.celular || !formData.genero || !formData.lateralidade) {
        errorMessage.value = "Por favor, preencha todos os campos obrigatórios (*).";
        return;
    }

    // Validação simples de tamanho de senha
    if (formData.senha.length < 8) {
        errorMessage.value = "A senha deve ter no mínimo 8 caracteres.";
        return;
    }

    errorMessage.value = '';
    step.value = 2;
    await buscarHorarios();
};

const buscarHorarios = async () => {
    loadingSlots.value = true;
    try {
        // Chama a Rota PÚBLICA nova que criamos no controller
        const response = await axios.get('/api/aulas/disponiveis-publico');
        horariosDisponiveis.value = response.data;
    } catch (error) {
        console.error("Erro ao buscar horários", error);
        errorMessage.value = "Não foi possível carregar os horários. Tente novamente.";
    } finally {
        loadingSlots.value = false;
    }
};

const selecionarHorario = (slot) => {
    aulaSelecionada.value = slot;
};

const formatarData = (isoDate) => {
    if (!isoDate) return '';
    const data = new Date(isoDate);
    // Formata dia/mês (Ex: 05/12)
    return new Intl.DateTimeFormat('pt-BR', { day: '2-digit', month: '2-digit' }).format(data);
};

// --- SUBMIT FINAL ---
const pularAgendamento = () => {
    aulaSelecionada.value = null;
    finalizarCadastro();
};

const finalizarCadastro = async () => {
    if (!aulaSelecionada.value && step.value === 2 && !confirm("Deseja finalizar o cadastro sem agendar a aula experimental?")) {
        return;
    }

    loadingSubmit.value = true;
    errorMessage.value = '';

    // Prepara payload (limpa máscaras para evitar erro 422)
    const payload = {
        ...formData,
        cpf: formData.cpf.replace(/\D/g, ''),
        celular: formData.celular.replace(/\D/g, ''),

        // Se houver aula selecionada, anexa os dados
        aula_interesse: aulaSelecionada.value ? {
            horario_agenda_id: aulaSelecionada.value.id_agenda,
            data_hora: aulaSelecionada.value.data_hora
        } : null
    };

    try {
        await axios.post('/api/register', payload);

        // Redireciona para login com flag de sucesso
        router.push('/login?cadastrado=true');
    } catch (error) {
        if (error.response?.data) {
             if (error.response.data.errors) {
                // Pega o primeiro erro da lista
                const firstErrorKey = Object.keys(error.response.data.errors)[0];
                errorMessage.value = error.response.data.errors[firstErrorKey][0];
             } else {
                errorMessage.value = error.response.data.message || 'Erro ao realizar cadastro.';
             }
        } else {
             errorMessage.value = 'Erro de conexão com o servidor.';
        }
    } finally {
        loadingSubmit.value = false;
    }
};
</script>

<style scoped>
.form-input {
  @apply w-full p-3 rounded-xl bg-[#0f1616] text-white placeholder-gray-500 border border-transparent focus:border-teal-600 focus:bg-[#151a1a] transition-all outline-none;
}
.btn-primary {
    @apply w-full py-3.5 rounded-xl bg-gradient-to-r from-teal-600 to-teal-500 hover:from-teal-500 hover:to-teal-400 text-white font-bold transition-all shadow-lg shadow-teal-900/20 flex justify-center items-center;
}
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: #1a1a1a;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #333;
  border-radius: 3px;
}
.animate-fade-in {
    animation: fadeIn 0.4s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
