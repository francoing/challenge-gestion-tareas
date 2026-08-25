<!--
  Formulario de tarea dentro de un modal. Sirve para alta y edición:
  si llega la prop `tarea`, precarga sus valores y cambia los textos.

  Emite 'guardar' con el payload; quien lo usa decide qué hacer con él.
  Los errores de validación (422) llegan por prop y se pintan campo por campo.
-->
<script setup>
import { computed, ref, watch } from 'vue'

import BaseModal from '@/common/components/BaseModal.vue'
import { ESTADOS } from '@/common/estados'

const props = defineProps({
  abierto: { type: Boolean, default: false },
  /** Tarea a editar. null = alta. */
  tarea: { type: Object, default: null },
  prioridades: { type: Array, default: () => [] },
  etiquetas: { type: Array, default: () => [] },
  guardando: { type: Boolean, default: false },
  /** @type {import('vue').PropType<import('@/api/ApiError').ApiError|null>} */
  error: { type: Object, default: null },
})

const emit = defineEmits(['cerrar', 'guardar'])

const esEdicion = computed(() => Boolean(props.tarea))

const FORMULARIO_VACIO = {
  titulo: '',
  descripcion: '',
  fecha_vencimiento: '',
  prioridad_id: '',
  estado: 'pendiente',
  etiquetas: [],
}

const form = ref({ ...FORMULARIO_VACIO, etiquetas: [] })

/** Vuelca la tarea al formulario, o lo limpia si es un alta. */
function precargar() {
  if (!props.tarea) {
    form.value = { ...FORMULARIO_VACIO, etiquetas: [] }

    return
  }

  form.value = {
    titulo: props.tarea.titulo,
    descripcion: props.tarea.descripcion,
    // El API devuelve null cuando no hay fecha; el input date necesita ''.
    fecha_vencimiento: props.tarea.fecha_vencimiento ?? '',
    prioridad_id: props.tarea.prioridad_id,
    estado: props.tarea.estado,
    // El API devuelve objetos; los checkboxes trabajan con ids.
    etiquetas: (props.tarea.etiquetas ?? []).map((etiqueta) => etiqueta.id),
  }
}

// Al abrir se precarga: si no, la próxima vez arrancaría con lo anterior.
watch(() => props.abierto, (abierto) => abierto && precargar())

/** Sólo los 422 traen errores por campo; el resto ya los mostró el toast. */
const errores = computed(() => (props.error?.esValidacion ? props.error : null))

function errorDe(campo) {
  return errores.value?.errorDe(campo) ?? null
}

function enviar() {
  const payload = {
    titulo: form.value.titulo,
    descripcion: form.value.descripcion,
    // El backend acepta null, pero un input date vacío devuelve ''.
    fecha_vencimiento: form.value.fecha_vencimiento || null,
    prioridad_id: form.value.prioridad_id,
    etiquetas: form.value.etiquetas,
  }

  // El estado sólo viaja al editar: en el alta el backend lo rechaza
  // a propósito, porque toda tarea nace pendiente.
  if (esEdicion.value) payload.estado = form.value.estado

  emit('guardar', payload)
}

const claseCampo =
  'block w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500'
const claseCampoConError = 'border-rose-400 focus:border-rose-500 focus:ring-rose-500'
</script>

<template>
  <BaseModal
    :abierto="abierto"
    :titulo="esEdicion ? 'Editar tarea' : 'Nueva tarea'"
    :bloqueado="guardando"
    @cerrar="emit('cerrar')"
  >
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

      <!-- El estado sólo se edita; en el alta siempre nace pendiente. -->
      <div v-if="esEdicion">
        <label for="estado" class="mb-1 block text-xs font-medium text-slate-700">Estado</label>
        <select id="estado" v-model="form.estado" :class="[claseCampo, errorDe('estado') && claseCampoConError]">
          <option v-for="e in ESTADOS" :key="e.valor" :value="e.valor">{{ e.texto }}</option>
        </select>
        <p v-if="errorDe('estado')" class="mt-1 text-xs text-rose-600">{{ errorDe('estado') }}</p>
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

      <p v-if="!esEdicion" class="text-xs text-slate-400">La tarea se crea en estado «Pendiente».</p>
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
        <template v-if="guardando">Guardando…</template>
        <template v-else>{{ esEdicion ? 'Guardar cambios' : 'Crear tarea' }}</template>
      </button>
    </template>
  </BaseModal>
</template>
