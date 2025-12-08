<template>
  <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
  >
    <div v-if="estado.ativo" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/80 backdrop-blur-sm">

        <div class="bg-[#242424] border w-full max-w-sm p-8 rounded-2xl shadow-2xl text-center transition-colors"
             :class="{
                'border-teal-500/30': estado.tipo === 'sucesso',
                'border-red-500/30': estado.tipo === 'erro',
                'border-yellow-500/30': estado.tipo === 'confirmacao'
             }">

            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full mb-6 border transition-colors"
                 :class="{
                    'bg-teal-900/30 border-teal-500/20 text-teal-400': estado.tipo === 'sucesso',
                    'bg-red-900/30 border-red-500/20 text-red-400': estado.tipo === 'erro',
                    'bg-yellow-900/30 border-yellow-500/20 text-yellow-400': estado.tipo === 'confirmacao'
                 }">

                <svg v-if="estado.tipo === 'sucesso'" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                <svg v-else-if="estado.tipo === 'erro'" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            </div>

            <h3 class="text-xl font-bold text-white mb-2">{{ estado.titulo }}</h3>
            <p class="text-gray-400 mb-8 text-sm leading-relaxed">{{ estado.mensagem }}</p>

            <div v-if="estado.tipo === 'confirmacao'" class="flex gap-3">
                <button @click="fechar" class="flex-1 py-3 rounded-xl border border-gray-600 text-gray-300 hover:bg-gray-700 font-semibold transition-all">
                    Cancelar
                </button>
                <button @click="confirmar" class="flex-1 py-3 rounded-xl bg-yellow-600 text-white hover:bg-yellow-500 font-semibold shadow-lg shadow-yellow-900/20 transition-all">
                    Confirmar
                </button>
            </div>

            <button v-else @click="fechar" class="w-full font-semibold py-3 rounded-xl transition-all shadow-lg text-white"
                :class="estado.tipo === 'sucesso' ? 'bg-teal-600 hover:bg-teal-500' : 'bg-red-600 hover:bg-red-500'">
                OK
            </button>
        </div>
    </div>
  </Transition>
</template>

<script setup>
import { useAlert } from '../composables/useAlert';
const { estado, fechar, confirmar } = useAlert();
</script>
