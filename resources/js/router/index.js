import { createRouter, createWebHistory } from 'vue-router';

// Componentes principais
import Login from '../components/Login.vue';
import Dashboard from '../components/Dashboard.vue';
import Register from '../components/Register.vue';

// Componentes das telas do Dashboard
import Agenda from '../components/dashboard/Agenda.vue';
import HorariosAgenda from '../components/dashboard/HorariosAgenda.vue';
import Clientes from '../components/dashboard/Clientes.vue';
import DetalhesCliente from '../components/dashboard/DetalhesCliente.vue';
import AvaliacaoPostural from '../components/dashboard/AvaliacaoPostural.vue';

const routes = [
    { path: '/login', name: 'login', component: Login },
    { path: '/register', name: 'register', component: Register },
    { path: '/', redirect: '/login' },
    {
        path: '/dashboard',
        component: Dashboard,
        meta: { requiresAuth: true },
        redirect: { name: 'dashboard-agenda' },
        children: [
            { path: 'agenda', name: 'dashboard-agenda', component: Agenda },
            { path: 'horarios', name: 'dashboard-horarios', component: HorariosAgenda },
            { path: 'clientes', name: 'dashboard-clientes', component: Clientes },
            {
                path: 'clientes/:id',
                name: 'detalhes-cliente',
                component: DetalhesCliente,
                props: true
            },
            {
                path: 'clientes/:id/avaliacao',
                name: 'avaliacao-postural',
                component: AvaliacaoPostural,
                props: true
            }
        ]
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// A guarda de rota permanece a mesma
router.beforeEach((to, from, next) => {
  const isLoggedIn = !!localStorage.getItem('authToken');
  if (to.meta.requiresAuth && !isLoggedIn) {
    next({ name: 'login' });
  } else {
    next();
  }
});

export default router;
