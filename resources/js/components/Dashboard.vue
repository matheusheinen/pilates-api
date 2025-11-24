<template>
  <div class="flex bg-gray-900 text-white min-h-screen">
    <nav class="fixed top-0 left-0 h-screen w-64 bg-[#151515] overflow-y-auto z-50">
      <div class="p-6">
        <img :src="logoUrl" alt="Logo" class="w-32 mb-8"/>

        <ul class="space-y-2">
          <template v-for="item in menuItems" :key="item.label">

            <hr v-if="item.action === 'logout'" class="my-4 border-gray-700/50" />

            <li :class="{'mt-2': item.action !== 'logout'}">
                <router-link
                  v-if="item.action !== 'logout'"
                  :to="{ name: item.routeName }"
                  class="flex items-center space-x-3 p-2 rounded-lg w-full text-left transition duration-150 text-[#f0f0f0] hover:bg-[#242424]"
                  active-class="bg-[#009088] text-white shadow-md"
                >
                  <component :is="item.icon" class="w-5 h-5" />
                  <span class="font-medium text-sm">{{ item.label }}</span>
                </router-link>

                <button
                  v-else
                  @click="handleLogout"
                  class="flex items-center space-x-3 p-2 rounded-lg w-full text-left transition duration-150 text-red-400 hover:bg-red-500/10 hover:text-red-300"
                >
                  <component :is="item.icon" class="w-5 h-5" />
                  <span class="font-medium text-sm">{{ item.label }}</span>
                </button>
            </li>
          </template>
        </ul>
      </div>
    </nav>

    <main class="flex-1 ml-64 p-8">
      <header class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-white">Bem-vindo, <span class="text-teal-500">{{ usuario.nome }}</span></h1>
          <p class="text-gray-400 text-sm mt-1">Painel de Controle - Sistema Pilates</p>
        </div>
      </header>

      <section>
        <router-view />
      </section>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
// ÍCONES: Adicionado Banknote para Mensalidades
import { UsersRound, CalendarCheck, Clock, LogOut, CreditCard, Banknote } from 'lucide-vue-next';
import logoUrl from '../../assets/logo.png';

const router = useRouter();
const usuario = ref(JSON.parse(localStorage.getItem('userData')) || {});

// LISTA DE ITENS ATUALIZADA
const menuItems = ref([
    { label: "Agenda", icon: CalendarCheck, routeName: 'dashboard-agenda' },
    { label: "Horários da Agenda", icon: Clock, routeName: 'dashboard-horarios' },
    { label: "Clientes", icon: UsersRound, routeName: 'dashboard-clientes' },
    { label: "Planos", icon: CreditCard, routeName: 'Planos' },
    { label: "Mensalidades", icon: Banknote, routeName: 'Mensalidades' }, // <--- NOVO ITEM
    { label: "Sair", icon: LogOut, action: 'logout' },
]);


const handleLogout = async () => {
    try {
        await axios.post('/api/logout');
    } catch (error) {
        console.error("Erro ao notificar logout:", error);
    }

    localStorage.removeItem('authToken');
    localStorage.removeItem('userData');

    delete axios.defaults.headers.common['Authorization'];

    router.push({ name: 'login' });
};

onMounted(() => {
    // Verifica se tem token
    if (!localStorage.getItem('authToken')) {
        router.push({ name: 'login' });
    }
});
</script>
