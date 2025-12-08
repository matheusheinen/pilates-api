<template>
  <div class="bg-[#151515] min-h-screen text-white p-6">

    <div v-if="loading" class="flex flex-col items-center justify-center h-[60vh] text-gray-500">
        <svg class="animate-spin h-12 w-12 mb-4 text-teal-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
        <span class="text-lg font-medium animate-pulse">Carregando dados do aluno...</span>
    </div>

    <div v-else-if="cliente">
      <div class="flex justify-between items-center mb-6 max-w-4xl mx-auto">
        <div>
          <h3 class="text-2xl font-bold">Editar Cliente</h3>
          <p class="text-gray-400 text-sm">Atualize os dados de <span class="text-teal-400 font-medium">{{ cliente.nome }}</span>.</p>
        </div>
        <router-link
          :to="{ name: 'detalhes-cliente', params: { id: props.id } }"
          class="text-gray-400 hover:text-white transition-colors flex items-center gap-2 text-sm bg-white/5 px-4 py-2 rounded-lg border border-white/5 hover:border-white/20"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
          Voltar
        </router-link>
      </div>

      <div class="bg-[#1e1e1e] border border-white/5 p-8 rounded-2xl shadow-xl w-full max-w-4xl mx-auto">

        <form @submit.prevent="submitForm" class="space-y-6">

          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
              <label class="label-custom">Nome Completo</label>
              <input v-model="form.nome" type="text" required class="form-input">
            </div>

            <div class="md:col-span-2">
              <label class="label-custom">Email</label>
              <input v-model="form.email" type="email" required class="form-input">
            </div>

            <div>
              <label class="label-custom">CPF (Não editável)</label>
              <input
                v-model="form.cpf"
                type="text"
                disabled
                class="form-input opacity-50 cursor-not-allowed bg-[#151515]"
              >
            </div>

            <div>
              <label class="label-custom">Celular</label>
              <input
                v-model="form.celular"
                type="text"
                @input="formatarCelular"
                maxlength="15"
                placeholder="(00) 00000-0000"
                class="form-input"
              >
            </div>

            <div>
              <label class="label-custom">Data de Nascimento</label>
              <input v-model="form.data_nascimento" type="date" class="form-input text-gray-300">
            </div>

            <div>
              <label class="label-custom">Profissão</label>
              <input v-model="form.profissao" type="text" placeholder="Ex: Advogada" class="form-input">
            </div>

            <div>
              <label class="label-custom">Gênero</label>
              <div class="relative">
                <select v-model="form.genero" class="form-input appearance-none cursor-pointer">
                    <option value="" disabled>Selecione</option>
                    <option value="feminino">Feminino</option>
                    <option value="masculino">Masculino</option>
                    <option value="outro">Outro</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
              </div>
            </div>

            <div>
              <label class="label-custom">Lateralidade</label>
              <div class="relative">
                <select v-model="form.lateralidade" class="form-input appearance-none cursor-pointer">
                    <option value="" disabled>Selecione</option>
                    <option value="destro">Destro</option>
                    <option value="canhoto">Canhoto</option>
                    <option value="ambidestro">Ambidestro</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </div>
              </div>
            </div>

            <div class="md:col-span-2 border-t border-gray-700/50 my-2 pt-6">
                <p class="text-teal-400 text-xs font-bold uppercase tracking-widest mb-4">Alterar Senha (Opcional)</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="label-custom">Nova Senha</label>
                        <input v-model="form.senha" type="password" placeholder="Deixe em branco para manter" class="form-input">
                    </div>

                    <div>
                        <label class="label-custom">Confirmar Nova Senha</label>
                        <input v-model="form.confirmarSenha" type="password" placeholder="Repita a nova senha" class="form-input">
                    </div>
                </div>
            </div>
          </div>

          <button
            type="submit"
            :disabled="saving"
            class="w-full bg-teal-600 hover:bg-teal-500 text-white font-bold py-4 rounded-xl transition-all shadow-lg shadow-teal-900/20 transform hover:-translate-y-0.5 mt-6 flex justify-center items-center gap-2"
          >
            <svg v-if="saving" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            <span>{{ saving ? 'Salvando Alterações...' : 'Salvar Alterações' }}</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import axios from 'axios';
import { useRoute, useRouter } from 'vue-router';
// 1. IMPORTAR ALERT
import { useAlert } from '../../composables/useAlert';

const route = useRoute();
const router = useRouter();
const props = defineProps(['id']);
// 2. EXTRAIR MÉTODOS
const { mostrarSucesso, mostrarErro } = useAlert();

const cliente = ref(null);
const loading = ref(true);
const saving = ref(false);
// errorMessage removido

const form = reactive({
  nome: '',
  email: '',
  cpf: '',
  celular: '',
  data_nascimento: '',
  genero: '',
  profissao: '',
  lateralidade: '',
  senha: '',
  confirmarSenha: ''
});

// Helpers de formatação
const formatarCelular = (event) => {
  let value = event.target.value.replace(/\D/g, '');
  if (value.length > 11) value = value.slice(0, 11);
  value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
  value = value.replace(/(\d)(\d{4})$/, '$1-$2');
  form.celular = value;
};

const aplicarMascaraCPF = (cpfLimpo) => {
    if (!cpfLimpo) return '';
    let value = cpfLimpo.replace(/\D/g, '');
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    return value;
};

const aplicarMascaraCelular = (celLimpo) => {
    if (!celLimpo) return '';
    let value = celLimpo.replace(/\D/g, '');
    if (value.length > 11) value = value.slice(0, 11);
    value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
    value = value.replace(/(\d)(\d{4})$/, '$1-$2');
    return value;
}

const fetchCliente = async () => {
  loading.value = true;
  try {
    const response = await axios.get(`/api/usuarios/${props.id}`);
    cliente.value = response.data;

    form.nome = cliente.value.nome;
    form.email = cliente.value.email;
    form.cpf = aplicarMascaraCPF(cliente.value.cpf);
    form.celular = aplicarMascaraCelular(cliente.value.celular);
    form.data_nascimento = cliente.value.data_nascimento;
    form.profissao = cliente.value.profissao || '';
    form.genero = cliente.value.genero ? cliente.value.genero.toLowerCase() : '';

    const lat = cliente.value.lateralidade || cliente.value.lateridade;
    form.lateralidade = lat ? lat.toLowerCase() : '';

  } catch (error) {
    console.error("Erro ao buscar cliente:", error);
    mostrarErro("Erro ao carregar os dados do cliente.");
  } finally {
    loading.value = false;
  }
};

const submitForm = async () => {
  saving.value = true;

  if (form.senha) {
      if (form.senha.length < 8) {
        mostrarErro("A nova senha deve ter no mínimo 8 caracteres.");
        saving.value = false;
        return;
      }
      if (form.senha !== form.confirmarSenha) {
          mostrarErro("A confirmação da senha não confere.");
          saving.value = false;
          return;
      }
  }

  const payload = {
      ...form,
      celular: form.celular ? form.celular.replace(/\D/g, '') : null,
      lateralidade: form.lateralidade,
  };

  delete payload.cpf;
  delete payload.confirmarSenha;

  if (!payload.senha) {
      delete payload.senha;
  }

  try {
    await axios.put(`/api/usuarios/${props.id}`, payload);

    // 3. SUCESSO MODERNO
    mostrarSucesso("Dados atualizados com sucesso!");

    // Pequeno delay para o usuário ver a mensagem antes de sair
    setTimeout(() => {
        router.push({ name: 'detalhes-cliente', params: { id: props.id } });
    }, 1500);

  } catch (error) {
    if (error.response && error.response.data) {
         if(error.response.data.errors) {
            const firstErrorKey = Object.keys(error.response.data.errors)[0];
            mostrarErro(error.response.data.errors[firstErrorKey][0]);
         } else {
            mostrarErro(error.response.data.message || "Ocorreu um erro ao salvar.");
         }
    } else {
        mostrarErro("Ocorreu um erro ao salvar as alterações.");
    }
  } finally {
    saving.value = false;
  }
};

onMounted(fetchCliente);
</script>

<style scoped>
.form-input {
  @apply w-full p-4 rounded-xl bg-[#242424] border border-[#333] text-white placeholder-gray-500 focus:border-teal-500 focus:ring-1 focus:ring-teal-500 transition-all outline-none;
}
.label-custom {
  @apply block text-xs font-medium text-gray-400 mb-2 ml-1 uppercase tracking-wide;
}
</style>
