<template>
  <div class="bg-gray-900 min-h-screen text-white pb-24">

    <header class="bg-[#151515] px-6 py-4 flex justify-between items-center sticky top-0 z-30 border-b border-gray-800/50 shadow-lg">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-teal-600 rounded-lg flex items-center justify-center font-bold text-white shadow-lg shadow-teal-900/20">P</div>
        <div>
          <h1 class="text-sm font-bold text-gray-200 leading-tight">Pilates e Bem-Estar</h1>
          <p class="text-[10px] text-gray-500 font-medium">Bem-vindo, {{ primeiroNome }}</p>
        </div>
      </div>

      <button @click="logout" class="p-2 text-gray-400 hover:text-red-400 transition-colors rounded-full hover:bg-white/5">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
      </button>
    </header>

    <main class="p-4 sm:p-6 max-w-md mx-auto">
        <router-view v-slot="{ Component }">
            <transition name="fade" mode="out-in">
                <component :is="Component" />
            </transition>
        </router-view>
    </main>

    <nav class="fixed bottom-0 left-0 w-full bg-[#151515]/95 backdrop-blur-md border-t border-gray-800 flex justify-around items-center py-3 z-50 safe-area-pb shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.3)]">

      <router-link :to="{ name: 'aluno-home' }" class="nav-item" active-class="active">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        <span class="text-[10px] font-medium mt-1">Início</span>
      </router-link>

      <router-link :to="{ name: 'aluno-agenda' }" class="nav-item" active-class="active">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/></svg>
        <span class="text-[10px] font-medium mt-1">Agenda</span>
      </router-link>

      <router-link :to="{ name: 'aluno-financeiro' }" class="nav-item" active-class="active">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
        <span class="text-[10px] font-medium mt-1">Financeiro</span>
      </router-link>

      <router-link :to="{ name: 'aluno-perfil' }" class="nav-item" active-class="active">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <span class="text-[10px] font-medium mt-1">Perfil</span>
      </router-link>

    </nav>
    <AlertMobile />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import AlertMobile from '../AlertMobile.vue';

const router = useRouter();
const usuario = ref(JSON.parse(localStorage.getItem('userData')) || {});

const primeiroNome = computed(() => {
    return usuario.value.nome ? usuario.value.nome.split(' ')[0] : 'Aluno';
});

const logout = async () => {
    if(!confirm("Deseja sair do aplicativo?")) return;
    try { await axios.post('/api/logout'); } catch (e) {}
    localStorage.removeItem('authToken');
    localStorage.removeItem('userData');
    router.push({ name: 'login' });
};
</script>

<style scoped>
.nav-item {
  @apply flex flex-col items-center text-gray-500 transition-all duration-200 w-full py-1;
}
.nav-item.active {
  @apply text-teal-500 scale-105;
}
.safe-area-pb {
    padding-bottom: env(safe-area-inset-bottom, 12px);
}
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>
