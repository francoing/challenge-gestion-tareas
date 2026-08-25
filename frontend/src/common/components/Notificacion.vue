<!--
  Toasts de error. Se suscribe a alFallar() de api/http.js, que emite los
  fallos que NO son de validación: los 422 los pinta el formulario.

  Se monta una sola vez en App.vue.
-->
<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'

import { alFallar } from '@/api/http'

const DURACION_MS = 6000

/** @type {import('vue').Ref<Array<{id: number, mensaje: string, status: number}>>} */
const notificaciones = ref([])

let siguienteId = 0
const temporizadores = new Map()
let desuscribir = null

function quitar(id) {
  clearTimeout(temporizadores.get(id))
  temporizadores.delete(id)
  notificaciones.value = notificaciones.value.filter((n) => n.id !== id)
}

function programarCierre(id) {
  clearTimeout(temporizadores.get(id))
  temporizadores.set(id, setTimeout(() => quitar(id), DURACION_MS))
}

/**
 * @param {import('@/api/ApiError').ApiError} error
 */
function mostrar(error) {
  // Si el mismo mensaje ya está en pantalla, se reinicia su temporizador en
  // lugar de apilar otro igual: reintentar tres veces con el backend caído
  // no debería llenar la pantalla de toasts idénticos.
  const existente = notificaciones.value.find((n) => n.mensaje === error.message)

  if (existente) {
    programarCierre(existente.id)

    return
  }

  const id = siguienteId++
  notificaciones.value.push({ id, mensaje: error.message, status: error.status })
  programarCierre(id)
}

onMounted(() => {
  desuscribir = alFallar(mostrar)
})

onBeforeUnmount(() => {
  desuscribir?.()
  temporizadores.forEach((t) => clearTimeout(t))
  temporizadores.clear()
})
</script>

<template>
  <!-- aria-live: los lectores de pantalla anuncian el error sin robar el foco -->
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
        v-for="n in notificaciones"
        :key="n.id"
        class="pointer-events-auto flex w-full max-w-sm items-start gap-3 rounded-lg border border-rose-200 bg-white p-3 shadow-lg"
      >
        <svg class="mt-0.5 h-4 w-4 shrink-0 text-rose-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
          <path
            fill-rule="evenodd"
            d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16ZM10 5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z"
            clip-rule="evenodd"
          />
        </svg>

        <p class="flex-1 text-sm text-slate-700">{{ n.mensaje }}</p>

        <button
          type="button"
          class="shrink-0 rounded p-0.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600"
          aria-label="Cerrar aviso"
          @click="quitar(n.id)"
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
