<template>
  <div class="flex">
    <nav class="fixed top-0 left-0 h-screen w-64 bg-[#151515] overflow-y-auto z-50">
      <div class="p-6">
        <h1 class="text-2xl font-semibold mb-8 text-[#009088]">Menu</h1>
        <ul class="space-y-4">
          <li v-for="item in menuItems" :key="item.label">
            <button @click="selectMenu(item)" :class="['flex items-center space-x-3 p-2 rounded-xl w-full text-left transition duration-150', selectedMenu.label === item.label ? 'bg-[#242424] text-[#009088] font-medium' : 'text-[#f0f0f0] hover:bg-[#242424]']">
              <component :is="item.icon" class="w-5 h-5" />
              <span class="font-normal text-sm">{{ item.label }}</span>
            </button>
          </li>
        </ul>
      </div>
    </nav>

    <main class="flex-grow p-10 ml-64">
      <header class="flex justify-end items-center mb-8">
        <div class="text-right">
          <p class="font-semibold text-[#f0f0f0]">{{ usuario.nome }}</p>
          <p class="text-sm text-[#a0a0a0]">{{ usuario.tipo }}</p>
        </div>
      </header>

      <section>
        <div class="bg-[#151515] p-6 rounded-xl min-h-[calc(100vh-150px)]">
          <h3 class="text-lg font-semibold mb-2 text-white">{{ selectedMenu.label }}</h3>
          <p class="text-[#a0a0a0] mb-4">{{ selectedMenu.description }}</p>
          <div v-html="selectedMenu.content" class="text-white"></div>
        </div>
      </section>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
// Importe os ícones que vamos usar
import { UsersRound, Wallet, CalendarCheck } from 'lucide-vue-next';

const router = useRouter();
const usuario = ref(JSON.parse(localStorage.getItem('userData')) || {});

// Associe os componentes dos ícones aos itens do menu
const menuItems = ref([
    { label: "Clientes", icon: UsersRound, description: "Visualize todos seus clientes", content: "Conteúdo da área de clientes aqui..." },
    { label: 'Pagamentos', icon: Wallet, description: 'Aqui você gerencia todos os pagamentos.', content: "Conteúdo da área de pagamentos aqui..." },
    { label: 'Agenda', icon: CalendarCheck, description: 'Veja e edite a agenda.', content: "Conteúdo da agenda aqui..." },
]);

const selectedMenu = ref(menuItems.value[0]);

const selectMenu = (item) => {
  selectedMenu.value = item;
};

onMounted(() => {
  if (!usuario.value.id) {
    // Se não houver dados do usuário, redireciona para o login
    router.push({ name: 'login' });
  }
});
</script>

<style>
.bg-sidebar-dark { background-color: #151515; }
.bg-sidebar-item-hover { background-color: #242424; }
.text-accent-green { color: #009088; }
.text-text-light { color: #f0f0f0; }
.text-text-muted { color: #a0a0a0; }
</style>
