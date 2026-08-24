<!--
  Muestra el estado de una tarea con color y etiqueta legible.
  Recibe el valor crudo del API ('en_progreso') y lo traduce.
-->
<script setup>
import { computed } from 'vue'

const props = defineProps({
  estado: { type: String, required: true },
})

const ESTADOS = {
  pendiente: { texto: 'Pendiente', clases: 'bg-slate-100 text-slate-700 ring-slate-200' },
  en_progreso: { texto: 'En progreso', clases: 'bg-amber-100 text-amber-800 ring-amber-200' },
  completada: { texto: 'Completada', clases: 'bg-emerald-100 text-emerald-800 ring-emerald-200' },
}

// Si el backend sumara un estado nuevo, se muestra el valor crudo
// en lugar de romper el renderizado.
const estilo = computed(
  () => ESTADOS[props.estado] ?? { texto: props.estado, clases: 'bg-slate-100 text-slate-700 ring-slate-200' },
)
</script>

<template>
  <span
    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
    :class="estilo.clases"
  >
    {{ estilo.texto }}
  </span>
</template>
