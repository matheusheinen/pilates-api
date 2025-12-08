<template>
  <Transition enter-active-class="transition-opacity duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition-opacity duration-300" leave-from-class="opacity-100" leave-to-class="opacity-0">
    <div v-if="estado.ativo" class="fixed inset-0 z-[9999] bg-black/60 backdrop-blur-[2px]" @click="fechar"></div>
  </Transition>

  <Transition enter-active-class="transition-transform duration-300 ease-out" enter-from-class="translate-y-full" enter-to-class="translate-y-0" leave-active-class="transition-transform duration-200 ease-in" leave-from-class="translate-y-0" leave-to-class="translate-y-full">
    <div v-if="estado.ativo" class="fixed bottom-0 left-0 right-0 z-[10000] bg-[#1e1e1e] rounded-t-3xl border-t border-gray-700 shadow-[0_-10px_40px_rgba(0,0,0,0.5)] p-6 pb-10">

        <div class="w-12 h-1.5 bg-gray-600 rounded-full mx-auto mb-6"></div>

        <div class="flex items-start gap-4">
            <div class="flex-shrink-0 h-12 w-12 rounded-full flex items-center justify-center transition-colors"
                 :class="{
                    'bg-teal-900/50 text-teal-400': estado.tipo === 'sucesso',
                    'bg-red-900/50 text-red-400': estado.tipo === 'erro',
                    'bg-yellow-900/50 text-yellow-400': estado.tipo === 'confirmacao'
                 }">
                 <svg v-if="estado.tipo === 'sucesso'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                 <svg v-else-if="estado.tipo === 'erro'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                 <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>

            <div class="flex-1">
                <h3 class="text-lg font-bold text-white mb-1">{{ estado.titulo }}</h3>
                <p class="text-gray-400 text-sm">{{ estado.mensagem }}</p>
            </div>
        </div>

        <div v-if="estado.tipo === 'confirmacao'" class="mt-8 grid grid-cols-2 gap-3">
             <button @click="fechar" class="w-full font-bold py-4 rounded-xl text-gray-300 border border-gray-600 bg-transparent active:scale-[0.98] transition-transform">
                Cancelar
            </button>
            <button @click="confirmar" class="w-full font-bold py-4 rounded-xl text-white bg-yellow-600 active:scale-[0.98] transition-transform">
                Sim, confirmar
            </button>
        </div>

        <button v-else @click="fechar" class="mt-8 w-full font-bold py-4 rounded-xl text-white active:scale-[0.98] transition-transform"
             :class="estado.tipo === 'sucesso' ? 'bg-teal-600' : 'bg-red-600'">
            {{ estado.tipo === 'sucesso' ? 'Continuar' : 'Fechar' }}
        </button>
    </div>
  </Transition>
</template>

<script setup>
import { useAlert } from '../composables/useAlert';
const { estado, fechar, confirmar } = useAlert();
</script>
