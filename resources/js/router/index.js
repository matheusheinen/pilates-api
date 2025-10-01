import { createRouter, createWebHistory } from 'vue-router';
import Login from '../components/Login.vue';

// Defina suas rotas aqui
const routes = [
    {
        path: '/login',
        name: 'login',
        component: Login
    },
    // Adicionaremos a rota do dashboard aqui depois
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
