import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Define a URL base da sua API Laravel
window.axios.defaults.baseURL = 'http://localhost:8000'; // Ou a URL do seu `php artisan serve`

// Adiciona o token de autenticação a todas as requisições, se ele existir
const token = localStorage.getItem('authToken');
if (token) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}
