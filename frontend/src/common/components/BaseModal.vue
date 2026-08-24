<!--
  Modal genérico: sólo aporta el contenedor, el fondo y el cierre.
  El contenido lo define quien lo usa, vía slots.
-->
<script setup>
import { onBeforeUnmount, watch } from 'vue'

const props = defineProps({
  abierto: { type: Boolean, default: false },
  titulo: { type: String, default: '' },
  // Mientras se guarda no se puede cerrar, para no cancelar a mitad de camino.
  bloqueado: { type: Boolean, default: false },
})

const emit = defineEmits(['cerrar'])

function cerrar() {
  if (!props.bloqueado) emit('cerrar')
}

function alPresionarTecla(evento) {
  if (evento.key === 'Escape') cerrar()
}

// El listener vive sólo mientras el modal está abierto, y se bloquea el
// scroll del fondo para que no se mueva detrás del diálogo.
watch(
  () => props.abierto,
  (abierto) => {
    document.body.style.overflow = abierto ? 'hidden' : ''

    if (abierto) document.addEventListener('keydown', alPresionarTecla)
    else document.removeEventListener('keydown', alPresionarTecla)
  },
)

onBeforeUnmount(() => {
  document.removeEventListener('keydown', alPresionarTecla)
  document.body.style.overflow = ''
})
</script>

<template>
  <Teleport to="body">
    <div v-if="abierto" class="fixed inset-0 z-50 flex items-start justify-center overflow-y-auto p-4 sm:p-6">
      <!-- Fondo: clickearlo cierra -->
      <div class="fixed inset-0 bg-slate-900/40" @click="cerrar" />

      <div
        role="dialog"
        aria-modal="true"
        :aria-label="titulo"
        class="relative z-10 my-8 w-full max-w-lg rounded-xl bg-white shadow-xl"
      >
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
          <h3 class="text-sm font-semibold text-slate-900">{{ titulo }}</h3>
          <button
            type="button"
            class="rounded p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 disabled:opacity-40"
            :disabled="bloqueado"
            aria-label="Cerrar"
            @click="cerrar"
          >
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path
                d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"
              />
            </svg>
          </button>
        </div>

        <div class="px-5 py-4">
          <slot />
        </div>

        <div v-if="$slots.pie" class="flex justify-end gap-2 border-t border-slate-200 px-5 py-4">
          <slot name="pie" />
        </div>
      </div>
    </div>
  </Teleport>
</template>
