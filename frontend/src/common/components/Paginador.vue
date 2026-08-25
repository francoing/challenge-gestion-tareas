<!--
  Paginador. Componente "tonto": no conoce el store ni la forma del `meta`
  de Laravel. Recibe números por props y avisa a qué página ir.

  Esa ignorancia es a propósito: si mañana el backend cambia el nombre de
  `last_page`, se toca quien lo usa, no este componente.
-->
<script setup>
import { computed } from 'vue'

const props = defineProps({
  paginaActual: { type: Number, required: true },
  ultimaPagina: { type: Number, required: true },
  /** Para el texto "Mostrando X–Y de Z". Opcionales: sin ellos no se muestra. */
  total: { type: Number, default: null },
  desde: { type: Number, default: null },
  hasta: { type: Number, default: null },
  /** Bloquea los botones mientras se está cargando la página nueva. */
  deshabilitado: { type: Boolean, default: false },
})

const emit = defineEmits(['cambiar'])

/** Páginas a cada lado de la actual dentro de la ventana. */
const RADIO = 1
/** Hasta este total se muestran todas, sin elipsis. */
const SIN_ELIPSIS = 7

const ELIPSIS = '…'

function rango(desde, hasta) {
  return Array.from({ length: hasta - desde + 1 }, (_, i) => desde + i)
}

/**
 * Ventana de páginas visibles.
 *
 * Con muchas páginas no se pueden dibujar todos los botones, así que se
 * muestran la primera, la última, y las vecinas de la actual. Los huecos
 * se marcan con una elipsis.
 *
 * Devuelve, por ejemplo: [1, '…', 7, 8, 9, '…', 20]
 */
const paginas = computed(() => {
  const { paginaActual: actual, ultimaPagina: ultima } = props

  if (ultima <= SIN_ELIPSIS) return rango(1, ultima)

  // Las vecinas, recortadas para no invadir la primera ni la última:
  // ésas se agregan aparte y siempre están.
  const inicio = Math.max(2, actual - RADIO)
  const fin = Math.min(ultima - 1, actual + RADIO)

  const items = [1]

  // Elipsis sólo si hay un salto real. Si el hueco es de una sola página,
  // conviene mostrar esa página en vez de puntos suspensivos.
  if (inicio > 3) items.push(ELIPSIS)
  else if (inicio === 3) items.push(2)

  items.push(...rango(inicio, fin))

  if (fin < ultima - 2) items.push(ELIPSIS)
  else if (fin === ultima - 2) items.push(ultima - 1)

  items.push(ultima)

  return items
})

const hayAnterior = computed(() => props.paginaActual > 1)
const haySiguiente = computed(() => props.paginaActual < props.ultimaPagina)

const muestraConteo = computed(() => props.total !== null && props.desde !== null && props.hasta !== null)

function ir(pagina) {
  // Se ignora el click en la página actual y en las elipsis.
  if (pagina === props.paginaActual || pagina === ELIPSIS || props.deshabilitado) return

  emit('cambiar', pagina)
}

const BOTON = 'inline-flex h-8 min-w-8 items-center justify-center rounded-md px-2 text-sm transition disabled:cursor-not-allowed disabled:opacity-40'
</script>

<template>
  <!-- Una sola página no necesita controles. -->
  <nav v-if="ultimaPagina > 1" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between" aria-label="Paginación">
    <p v-if="muestraConteo" class="text-xs text-slate-500">
      Mostrando <span class="font-medium text-slate-700">{{ desde }}–{{ hasta }}</span>
      de <span class="font-medium text-slate-700">{{ total }}</span>
    </p>
    <span v-else />

    <div class="flex items-center gap-1">
      <button
        type="button"
        :class="[BOTON, 'border border-slate-300 text-slate-600 hover:bg-slate-100']"
        :disabled="!hayAnterior || deshabilitado"
        aria-label="Página anterior"
        @click="ir(paginaActual - 1)"
      >
        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
          <path
            fill-rule="evenodd"
            d="M12.79 5.23a.75.75 0 0 1 0 1.06L9.06 10l3.73 3.71a.75.75 0 1 1-1.06 1.06l-4.25-4.24a.75.75 0 0 1 0-1.06l4.25-4.24a.75.75 0 0 1 1.06 0Z"
            clip-rule="evenodd"
          />
        </svg>
      </button>

      <template v-for="(pagina, indice) in paginas" :key="indice">
        <!-- La elipsis no es un botón: no hay nada que clickear -->
        <span v-if="pagina === ELIPSIS" class="px-1 text-sm text-slate-400" aria-hidden="true">…</span>

        <button
          v-else
          type="button"
          :class="[
            BOTON,
            pagina === paginaActual
              ? 'bg-slate-900 font-medium text-white'
              : 'border border-slate-300 text-slate-600 hover:bg-slate-100',
          ]"
          :aria-current="pagina === paginaActual ? 'page' : undefined"
          :aria-label="`Página ${pagina}`"
          :disabled="deshabilitado"
          @click="ir(pagina)"
        >
          {{ pagina }}
        </button>
      </template>

      <button
        type="button"
        :class="[BOTON, 'border border-slate-300 text-slate-600 hover:bg-slate-100']"
        :disabled="!haySiguiente || deshabilitado"
        aria-label="Página siguiente"
        @click="ir(paginaActual + 1)"
      >
        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
          <path
            fill-rule="evenodd"
            d="M7.21 14.77a.75.75 0 0 1 0-1.06L10.94 10 7.21 6.29a.75.75 0 1 1 1.06-1.06l4.25 4.24a.75.75 0 0 1 0 1.06l-4.25 4.24a.75.75 0 0 1-1.06 0Z"
            clip-rule="evenodd"
          />
        </svg>
      </button>
    </div>
  </nav>
</template>
