import { createRouter, createWebHistory } from 'vue-router';

// Componentes
import Login from '../components/Login.vue';
import Register from '../components/Register.vue';

// Admin Components
import AdminLayout from '../components/admin/Admin.vue'; // Certifique-se que o arquivo existe
import Agenda from '../components/admin/Agenda.vue';
import HorariosAgenda from '../components/admin/HorariosAgenda.vue';
import Clientes from '../components/admin/Clientes.vue';
import DetalhesCliente from '../components/admin/DetalhesCliente.vue';
import AvaliacaoPostural from '../components/admin/AvaliacaoPostural.vue';
import EditarCliente from '../components/admin/EditarCliente.vue';
import VisualizarAvaliacao from '../components/admin/VisualizarAvaliacao.vue';
import Matricula from '../components/admin/Matricula.vue';
import Planos from '../components/admin/Planos.vue';
import Inscricoes from '../components/admin/Inscricoes.vue';
import EditarInscricao from '../components/admin/EditarInscricao.vue';
import Mensalidades from '../components/admin/Mensalidades.vue';
import Pagamentos from '../components/admin/Pagamentos.vue';

// Aluno Components
import StudentLayout from '../components/aluno/Aluno.vue';
import AlunoHome from '../components/aluno/Home.vue';
import AlunoFinanceiro from '../components/aluno/Financeiro.vue';
import AlunoPerfil from '../components/aluno/Perfil.vue';

const routes = [
    { path: '/login', name: 'login', component: Login },
    { path: '/register', name: 'register', component: Register },
    { path: '/', redirect: '/login' },

    // --- ADMIN ---
    {
        path: '/admin',
        component: AdminLayout,
        meta: { requiresAuth: true, role: 'admin' },
        redirect: { name: 'admin-agenda' }, // Redireciona para o nome CORRETO
        children: [
            // Definindo nomes padronizados "admin-..."
            { path: 'agenda', name: 'admin-agenda', component: Agenda },
            { path: 'horarios', name: 'admin-horarios', component: HorariosAgenda },
            { path: 'planos', name: 'admin-planos', component: Planos },
            { path: 'clientes', name: 'admin-clientes', component: Clientes },
            { path: 'mensalidades', name: 'admin-mensalidades', component: Mensalidades },
            { path: 'inscricoes', name: 'admin-inscricoes', component: Inscricoes },

            // Sub-rotas
            { path: 'clientes/:id', name: 'detalhes-cliente', component: DetalhesCliente, props: true },
            { path: 'clientes/:id/avaliacao', name: 'avaliacao-postural', component: AvaliacaoPostural, props: true },
            { path: 'clientes/:id/editar', name: 'editar-cliente', component: EditarCliente, props: true },
            { path: 'clientes/:id/matricula', name: 'matricula', component: Matricula, props: true },
            { path: 'clientes/:id/pagamentos', name: 'historico-pagamentos', component: Pagamentos, props: true },
            { path: 'avaliacoes/:id/visualizar', name: 'visualizar-avaliacao', component: VisualizarAvaliacao, props: true },
            { path: 'inscricoes/:id/editar', name: 'editar-inscricao', component: EditarInscricao, props: true }
        ]
    },

    // --- ALUNO ---
    {
        path: '/aluno',
        component: StudentLayout,
        meta: { requiresAuth: true, role: 'aluno' },
        redirect: { name: 'aluno-home' },
        children: [
            { path: 'aulas', name: 'aluno-home', component: AlunoHome },
            { path: 'financeiro', name: 'aluno-financeiro', component: AlunoFinanceiro },
            { path: 'perfil', name: 'aluno-perfil', component: AlunoPerfil },
        ]
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Guard de Proteção
router.beforeEach((to, from, next) => {
  const isLoggedIn = !!localStorage.getItem('authToken');
  const userData = localStorage.getItem('userData');
  const user = userData ? JSON.parse(userData) : null;

  if (to.meta.requiresAuth && !isLoggedIn) {
    next({ name: 'login' });
  } else if (isLoggedIn && to.meta.role) {
      // Redirecionamento de segurança
      if (to.meta.role === 'admin' && user.tipo !== 'admin') {
          next({ name: 'aluno-home' });
      } else if (to.meta.role === 'aluno' && user.tipo !== 'aluno') {
          next({ name: 'admin-agenda' });
      } else {
          next();
      }
  } else {
    next();
  }
});

export default router;
