<!--
  Select para cambiar el estado de una tarea desde el listado.

  Es un <select> nativo y no un menú propio a propósito: se opera con teclado
  y lo anuncian los lectores de pantalla sin que haya que implementar nada.
  Toma los colores de common/estados.js, los mismos que usa EstadoBadge.
-->
<script setup>
import { computed } from 'vue'

import { ESTADOS, buscarEstado } from '@/common/estados'

const props = defineProps({
  estado: { type: String, required: true },
  /** Mientras el PATCH viaja, para no encadenar cambios sobre la misma fila. */
  deshabilitado: { type: Boolean, default: false },
})

const emit = defineEmits(['cambiar'])

const estilo = computed(() => buscarEstado(props.estado))

/**
 * El prop es siempre la fuente de verdad de lo que se ve.
 *
 * Al elegir una opción el navegador ya cambió el value del <select>. Se lo
 * devuelve al valor del prop en el acto y se emite el cambio: si el PATCH
 * sale bien, el store actualiza la tarea y el prop baja el valor nuevo; si
 * falla, el select se quedó mostrando el estado real y no una mentira.
 */
function alElegir(evento) {
  const elegido = evento.target.value

  evento.target.value = props.estado

  if (elegido !== props.estado) {
    emit('cambiar', elegido)
  }
}
</script>

<template>
  <select
    :value="estado"
    :disabled="deshabilitado"
    aria-label="Cambiar estado de la tarea"
    class="cursor-pointer rounded-full border-0 py-0.5 pl-2 pr-7 text-xs font-medium ring-1 ring-inset focus:ring-2 focus:ring-slate-500 disabled:cursor-wait disabled:opacity-60"
    :class="estilo.clases"
    @change="alElegir"
  >
    <option v-for="opcion in ESTADOS" :key="opcion.valor" :value="opcion.valor">
      {{ opcion.texto }}
    </option>
  </select>
</template>
