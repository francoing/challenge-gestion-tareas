<!--
  Formulario de tarea dentro de un modal.
  Emite 'guardar' con el payload; quien lo usa decide qué hacer con él.
  Los errores de validación (422) llegan por prop y se pintan campo por campo.
-->
<script setup>
import { computed, ref, watch } from 'vue'

import BaseModal from '@/common/components/BaseModal.vue'

const props = defineProps({
  abierto: { type: Boolean, default: false },
  prioridades: { type: Array, default: () => [] },
  etiquetas: { type: Array, default: () => [] },
  guardando: { type: Boolean, default: false },
  /** @type {import('vue').PropType<import('@/api/ApiError').ApiError|null>} */
  error: { type: Object, default: null },
})

const emit = defineEmits(['cerrar', 'guardar'])

const FORMULARIO_VACIO = {
  titulo: '',
  descripcion: '',
  fecha_vencimiento: '',
  prioridad_id: '',
  etiquetas: [],
}

const form = ref({ ...FORMULARIO_VACIO })

// Al abrir se limpia: si no, la próxima alta arrancaría con lo anterior.
watch(
  () => props.abierto,
  (abierto) => {
    if (abierto) form.value = { ...FORMULARIO_VACIO, etiquetas: [] }
  },
)

/** Sólo los 422 traen errores por campo; el resto ya los mostró el toast. */
const errores = computed(() => (props.error?.esValidacion ? props.error : null))

function errorDe(campo) {
  return errores.value?.errorDe(campo) ?? null
}

function enviar() {
  emit('guardar', {
    titulo: form.value.titulo,
    descripcion: form.value.descripcion,
    // El backend acepta null, pero un input date vacío devuelve ''.
    fecha_vencimiento: form.value.fecha_vencimiento || null,
    prioridad_id: form.value.prioridad_id,
    etiquetas: form.value.etiquetas,
  })
}

const claseCampo =
  'block w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500'
const claseCampoConError = 'border-rose-400 focus:border-rose-500 focus:ring-rose-500'
</script>

<template>
  <BaseModal :abierto="abierto" titulo="Nueva tarea" :bloqueado="guardando" @cerrar="emit('cerrar')">
    <form id="form-tarea" class="space-y-4" @submit.prevent="enviar">
      <div>
        <label for="titulo" class="mb-1 block text-xs font-medium text-slate-700">Título</label>
        <input
          id="titulo"
          v-model="form.titulo"
          type="text"
          maxlength="255"
          placeholder="Configurar el entorno de desarrollo"
          :class="[claseCampo, errorDe('titulo') && claseCampoConError]"
        />
        <p v-if="errorDe('titulo')" class="mt-1 text-xs text-rose-600">{{ errorDe('titulo') }}</p>
      </div>

      <div>
        <label for="descripcion" class="mb-1 block text-xs font-medium text-slate-700">Descripción</label>
        <textarea
          id="descripcion"
          v-model="form.descripcion"
          rows="3"
          placeholder="Detalle de lo que hay que hacer"
          :class="[claseCampo, errorDe('descripcion') && claseCampoConError]"
        />
        <p v-if="errorDe('descripcion')" class="mt-1 text-xs text-rose-600">{{ errorDe('descripcion') }}</p>
      </div>

      <div class="grid gap-4 sm:grid-cols-2">
        <div>
          <label for="prioridad" class="mb-1 block text-xs font-medium text-slate-700">Prioridad</label>
          <select
            id="prioridad"
            v-model="form.prioridad_id"
            :class="[claseCampo, errorDe('prioridad_id') && claseCampoConError]"
          >
            <option value="" disabled>Elegí una prioridad</option>
            <option v-for="p in prioridades" :key="p.id" :value="p.id">{{ p.prioridad }}</option>
          </select>
          <p v-if="errorDe('prioridad_id')" class="mt-1 text-xs text-rose-600">{{ errorDe('prioridad_id') }}</p>
        </div>

        <div>
          <label for="vence" class="mb-1 block text-xs font-medium text-slate-700">
            Vencimiento <span class="font-normal text-slate-400">(opcional)</span>
          </label>
          <input
            id="vence"
            v-model="form.fecha_vencimiento"
            type="date"
            :class="[claseCampo, errorDe('fecha_vencimiento') && claseCampoConError]"
          />
          <p v-if="errorDe('fecha_vencimiento')" class="mt-1 text-xs text-rose-600">
            {{ errorDe('fecha_vencimiento') }}
          </p>
        </div>
      </div>

      <fieldset>
        <legend class="mb-1 block text-xs font-medium text-slate-700">
          Etiquetas <span class="font-normal text-slate-400">(podés elegir varias)</span>
        </legend>
        <div class="flex flex-wrap gap-2">
          <label
            v-for="e in etiquetas"
            :key="e.id"
            class="inline-flex cursor-pointer items-center gap-1.5 rounded-md border border-slate-300 px-2.5 py-1.5 text-xs text-slate-700 hover:bg-slate-50 has-checked:border-slate-800 has-checked:bg-slate-800 has-checked:text-white"
          >
            <input v-model="form.etiquetas" type="checkbox" :value="e.id" class="sr-only" />
            {{ e.etiqueta }}
          </label>
        </div>
        <p v-if="errorDe('etiquetas')" class="mt-1 text-xs text-rose-600">{{ errorDe('etiquetas') }}</p>
      </fieldset>

      <p class="text-xs text-slate-400">La tarea se crea en estado «Pendiente».</p>
    </form>

    <template #pie>
      <button
        type="button"
        class="rounded-md px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 disabled:opacity-50"
        :disabled="guardando"
        @click="emit('cerrar')"
      >
        Cancelar
      </button>
      <button
        type="submit"
        form="form-tarea"
        class="rounded-md bg-slate-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-800 disabled:opacity-50"
        :disabled="guardando"
      >
        {{ guardando ? 'Guardando…' : 'Crear tarea' }}
      </button>
    </template>
  </BaseModal>
</template>
