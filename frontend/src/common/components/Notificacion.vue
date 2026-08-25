<!--
  Toasts de éxito y error. Componente presentacional: sólo lee del store
  de avisos y renderiza. Quién los emite se decide en otro lado
  (main.js para los errores del interceptor, las vistas para los éxitos).

  Se monta una sola vez en App.vue.
-->
<script setup>
import { storeToRefs } from 'pinia'

import { useNotificacionesStore } from '@/stores/notificaciones'

const store = useNotificacionesStore()
const { items } = storeToRefs(store)

const ESTILOS = {
  exito: {
    caja: 'border-emerald-200',
    icono: 'text-emerald-600',
    // Tilde
    trazo: 'M16.7 5.3a1 1 0 0 1 0 1.4l-7.5 7.5a1 1 0 0 1-1.4 0l-3.5-3.5a1 1 0 1 1 1.4-1.4l2.8 2.79 6.8-6.79a1 1 0 0 1 1.4 0Z',
  },
  error: {
    caja: 'border-rose-200',
    icono: 'text-rose-600',
    // Círculo con exclamación
    trazo: 'M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM10 5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z',
  },
}
</script>

<template>
  <!-- aria-live: los lectores de pantalla anuncian el aviso sin robar el foco -->
  <div
    class="pointer-events-none fixed inset-x-0 top-0 z-[60] flex flex-col items-center gap-2 p-4 sm:items-end sm:p-6"
    role="status"
    aria-live="polite"
  >
    <TransitionGroup
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="-translate-y-2 opacity-0"
      leave-active-class="transition duration-150 ease-in"
      leave-to-class="translate-y-1 opacity-0"
    >
      <div
        v-for="aviso in items"
        :key="aviso.id"
        class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-lg border bg-white p-3 shadow-lg"
        :class="ESTILOS[aviso.tipo].caja"
      >
        <svg
          class="mt-0.5 h-4 w-4 shrink-0"
          :class="ESTILOS[aviso.tipo].icono"
          viewBox="0 0 20 20"
          fill="currentColor"
          aria-hidden="true"
        >
          <path fill-rule="evenodd" :d="ESTILOS[aviso.tipo].trazo" clip-rule="evenodd" />
        </svg>

        <p class="flex-1 text-sm text-slate-700">{{ aviso.mensaje }}</p>

        <button
          type="button"
          class="shrink-0 rounded p-0.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
          aria-label="Cerrar aviso"
          @click="store.quitar(aviso.id)"
        >
          <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path
              d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"
            />
          </svg>
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>
