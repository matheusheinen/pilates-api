<template>
  <div class="flex bg-gray-900 text-white min-h-screen">
    <nav class="fixed top-0 left-0 h-screen w-64 bg-[#151515] overflow-y-auto z-50">
      <div class="p-6">
        <img :src="logoUrl" alt="Logo" class="w-32 mb-8"/>
        <ul class="space-y-2">
          <li v-for="item in menuItems" :key="item.label">
            <router-link
              :to="{ name: item.routeName }"
              class="flex items-center space-x-3 p-2 rounded-lg w-full text-left transition duration-150 text-[#f0f0f0] hover:bg-[#242424]"
              active-class="bg-[#242424] text-[#009088] font-medium"
            >
              <component :is="item.icon" class="w-5 h-5" />
              <span class="font-normal text-sm">{{ item.label }}</span>
            </router-link>
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
        <router-view />
      </section>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { UsersRound, Wallet, CalendarCheck, Clock } from 'lucide-vue-next';
import logoUrl from '../../assets/logo.png';

const router = useRouter();
const usuario = ref(JSON.parse(localStorage.getItem('userData')) || {});

// O menu agora aponta para os NOMES das rotas que definiremos no index.js
const menuItems = ref([
    { label: "Agenda", icon: CalendarCheck, routeName: 'dashboard-agenda' },
    { label: "Horários da Agenda", icon: Clock, routeName: 'dashboard-horarios' },
    { label: "Clientes", icon: UsersRound, routeName: 'dashboard-clientes' },
    // { label: 'Pagamentos', icon: Wallet, routeName: 'dashboard-pagamentos' },
]);

onMounted(() => {
  if (!usuario.value.id) {
    router.push({ name: 'login' });
  }
});
</script>
