<!--
  Muestra el estado de una tarea con color y etiqueta legible.
  Recibe el valor crudo del API ('en_progreso') y lo traduce.
-->
<script setup>
import { computed } from 'vue'

import { buscarEstado } from '@/common/estados'

const props = defineProps({
  estado: { type: String, required: true },
})

const estilo = computed(() => buscarEstado(props.estado))
</script>

<template>
  <span
    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
    :class="estilo.clases"
  >
    {{ estilo.texto }}
  </span>
</template>
