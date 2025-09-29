import './bootstrap';
import '/resources/css/app.scss';
import { createApp } from 'vue';

import App from './App.vue';
import router from './router/index.js'; // Usando o caminho completo

const app = createApp(App);

app.use(router);
app.mount('#app');
