<!--
  Barra de filtros del listado (el bonus del enunciado).

  Componente presentacional: no pide datos ni decide cuándo recargar.
  Sólo emite el objeto de filtros actualizado; el debounce y el disparo
  del request viven en el composable.
-->
<script setup>
import { computed, useSlots } from 'vue'

import { ESTADOS } from '@/common/estados'

const slots = useSlots()

const props = defineProps({
  /** Objeto de filtros: { estado, prioridad_id, vence_desde, vence_hasta, buscar } */
  modelValue: { type: Object, required: true },
  prioridades: { type: Array },
  deshabilitado: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'limpiar'])

/**
 * Crea un v-model por campo que emite un objeto NUEVO en cada cambio.
 *
 * Se evita mutar props.modelValue en el lugar: al emitir una referencia
 * distinta, en el padre alcanza con un watch común y no hace falta deep.
 */
function campo(nombre) {
  return computed({
    get: () => props.modelValue[nombre] ?? '',
    set: (valor) => emit('update:modelValue', { ...props.modelValue, [nombre]: valor }),
  })
}

const estado = campo('estado')
const prioridadId = campo('prioridad_id')
const venceDesde = campo('vence_desde')
const venceHasta = campo('vence_hasta')
const buscar = campo('buscar')

/** Para mostrar el botón de limpiar sólo cuando hay algo que limpiar. */
const hayFiltros = computed(() => Object.values(props.modelValue).some((valor) => valor !== '' && valor != null))

/** Una columna más sólo si quien lo usa puso algo en el slot de acciones. */
const columnas = computed(() => (slots.acciones ? 'lg:grid-cols-6' : 'lg:grid-cols-5'))

const claseCampo =
  'block w-full rounded-md border border-slate-300 px-2.5 py-1.5 text-sm text-slate-900 placeholder:text-slate-400 focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500 disabled:bg-slate-50 disabled:text-slate-400'
const claseEtiqueta = 'mb-1 block text-xs font-medium text-slate-600'
</script>

<template>
  <div class="mb-4 rounded-lg border border-slate-200 bg-white p-4">
    <div class="grid gap-3 sm:grid-cols-2" :class="columnas">
      <div class="lg:col-span-2">
        <label for="f-buscar" :class="claseEtiqueta">Buscar</label>
        <input
          id="f-buscar"
          v-model="buscar"
          type="search"
          placeholder="Título o descripción…"
          :class="claseCampo"
          :disabled="deshabilitado"
        />
      </div>

      <div>
        <label for="f-estado" :class="claseEtiqueta">Estado</label>
        <select id="f-estado" v-model="estado" :class="claseCampo" :disabled="deshabilitado">
          <option value="">Todos</option>
          <option v-for="e in ESTADOS" :key="e.valor" :value="e.valor">{{ e.texto }}</option>
        </select>
      </div>

      <div>
        <label for="f-prioridad" :class="claseEtiqueta">Prioridad</label>
        <select id="f-prioridad" v-model="prioridadId" :class="claseCampo" :disabled="deshabilitado">
          <option value="">Todas</option>
          <option v-for="p in prioridades" :key="p.id" :value="p.id">{{ p.prioridad }}</option>
        </select>
      </div>

      <div class="grid grid-cols-2 gap-2">
        <div>
          <label for="f-desde" :class="claseEtiqueta">Vence desde</label>
          <input
            id="f-desde"
            v-model="venceDesde"
            type="date"
            :max="venceHasta || undefined"
            :class="claseCampo"
            :disabled="deshabilitado"
          />
        </div>
        <div>
          <label for="f-hasta" :class="claseEtiqueta">Hasta</label>
          <!-- min evita el rango invertido antes de que el backend
               lo rechace con un 422 (regla after_or_equal). -->
          <input
            id="f-hasta"
            v-model="venceHasta"
            type="date"
            :min="venceDesde || undefined"
            :class="claseCampo"
            :disabled="deshabilitado"
          />
        </div>
      </div>

      <!-- items-end alinea el contenido con la base de los inputs, que
           arrancan más abajo por sus etiquetas. -->
      <div v-if="slots.acciones" class="flex items-end sm:col-span-2 lg:col-span-1">
        <slot name="acciones" />
      </div>
    </div>

    <div v-if="hayFiltros" class="mt-3 flex justify-end">
      <button
        type="button"
        class="text-xs font-medium text-slate-500 underline-offset-2 hover:text-slate-800 hover:underline"
        :disabled="deshabilitado"
        @click="emit('limpiar')"
      >
        Limpiar filtros
      </button>
    </div>
  </div>
</template>
