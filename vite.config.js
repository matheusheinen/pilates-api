import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
// import tailwindcss from '@tailwindcss/vite'; // <-- REMOVA ESTA LINHA

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                compilerOptions: {
                    isCustomElement: (tag) => tag.startsWith('ion-')
                }
            }
        }),
        // tailwindcss(), // <-- E REMOVA ESTA LINHA
    ],
});
