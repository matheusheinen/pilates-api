import './bootstrap';
import '../css/app.css';
// Remova o import do app.scss se for usar Tailwind, ou configure-o
// import '/resources/css/app.scss';
import { createApp } from 'vue';

import App from './App.vue';
import router from './router/index.js';

const app = createApp(App);

app.use(router);
app.mount('#app');
