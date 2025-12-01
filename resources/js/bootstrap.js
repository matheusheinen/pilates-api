import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// CORREÇÃO: Removemos o 'http://localhost:8000' fixo.
// Ao usar apenas '/', o axios vai usar o domínio atual do site automaticamente.
// Isso funciona tanto no localhost quanto no Railway.
window.axios.defaults.baseURL = '/';

// Adiciona o token de autenticação a todas as requisições, se ele existir
const token = localStorage.getItem('authToken');
if (token) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}
