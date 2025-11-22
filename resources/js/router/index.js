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
import EditarCliente from '../components/dashboard/EditarCliente.vue';
import VisualizarAvaliacao from '../components/dashboard/VisualizarAvaliacao.vue';
import Matricula from '../components/dashboard/Matricula.vue';
import Planos from '../components/dashboard/Planos.vue';
import Inscricoes from '../components/dashboard/Inscricoes.vue';
import EditarInscricao from '../components/dashboard/EditarInscricao.vue';

// ------------------------------------------

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
            { path: 'planos', name: 'Planos', component: Planos },
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
            },
            {
                path: 'clientes/:id/editar',
                name: 'EditarCliente',
                component: EditarCliente,
                props: true
            },
            {
                path: 'avaliacoes/:id/visualizar',
                name: 'VisualizarAvaliacao',
                component: VisualizarAvaliacao,
                props: true
            },
            {
                path: 'clientes/:id/matricula',
                name: 'matricula',
                component: Matricula,
                props: true
            },
            {
                // Rota de listagem (agora funciona com o filtro ?usuario_id=X)
                path: 'inscricoes',
                name: 'listagem-inscricoes',
                component: Inscricoes // Componente de Listagem
            },
            {
                // Rota de edição de um contrato específico
                path: 'inscricoes/:id/editar',
                name: 'editar-inscricao',
                component: EditarInscricao, // Componente de Edição
                props: true
            }

        ]
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
  const isLoggedIn = !!localStorage.getItem('authToken');
  if (to.meta.requiresAuth && !isLoggedIn) {
    next({ name: 'login' });
  } else {
    next();
  }
});

export default router;
