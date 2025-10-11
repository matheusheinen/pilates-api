<template>
  <div class="flex bg-gray-900 text-white min-h-screen">
    <nav class="fixed top-0 left-0 h-screen w-64 bg-[#151515] overflow-y-auto z-50">
      <div class="p-6">
        <img :src="logoUrl" alt="Logo" class="w-32 mb-8"/>
        <ul class="space-y-2">
          <li v-for="item in menuItems" :key="item.label">
            <button @click="selectMenu(item)" :class="['flex items-center space-x-3 p-2 rounded-lg w-full text-left transition duration-150', selectedMenu.label === item.label ? 'bg-[#242424] text-[#009088] font-medium' : 'text-[#f0f0f0] hover:bg-[#242424]']">
              <component :is="item.icon" class="w-5 h-5" />
              <span class="font-normal text-sm">{{ item.label }}</span>
            </button>
          </li>
        </ul>
      </div>
    </nav>

    <main class="flex-grow p-6 sm:p-10 ml-64">
      <header class="flex justify-end items-center mb-8">
        <div class="text-right">
          <p class="font-semibold">{{ usuario.nome }}</p>
          <p class="text-sm text-[#a0a0a0] capitalize">{{ usuario.tipo }}</p>
        </div>
      </header>

      <section>
        <component :is="selectedMenu.component" />
      </section>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
// Importa os ícones que vamos usar
import { UsersRound, Wallet, CalendarCheck, Clock, FilePlus } from 'lucide-vue-next';
import logoUrl from '../../assets/logo.png';

// 1. Importe os novos componentes que criámos
import Agenda from './dashboard/Agenda.vue';
import HorariosAgenda from './dashboard/HorariosAgenda.vue';
import Clientes from './dashboard/Clientes.vue';
// import CadastrarAluno from './dashboard/CadastrarAluno.vue';
// import Pagamentos from './dashboard/Pagamentos.vue';

const router = useRouter();
const usuario = ref(JSON.parse(localStorage.getItem('userData')) || {});

// 2. Atualize o menu para associar cada item a um componente
const menuItems = ref([
    { label: "Agenda", icon: CalendarCheck, component: Agenda },
    { label: "Horários da Agenda", icon: Clock, component: HorariosAgenda },
    { label: "Clientes", icon: UsersRound, component: Clientes },
    // { label: 'Cadastrar Aluno', icon: FilePlus, component: CadastrarAluno },
    // { label: 'Pagamentos', icon: Wallet, component: Pagamentos },
]);

// Define a "Agenda" como o item inicial
const selectedMenu = ref(menuItems.value[0]);

const selectMenu = (item) => {
  selectedMenu.value = item;
};

onMounted(() => {
  if (!usuario.value.id) {
    router.push({ name: 'login' });
  }
});
</script>
