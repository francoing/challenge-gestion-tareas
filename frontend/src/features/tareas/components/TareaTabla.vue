<!--
  Tabla del listado. Componente "tonto": recibe las tareas por prop
  y no sabe de dónde salieron.
-->
<script setup>
import EstadoBadge from '@/common/components/EstadoBadge.vue'

defineProps({
  tareas: { type: Array, required: true },
})

const emit = defineEmits(['editar', 'eliminar'])

const PRIORIDADES = {
  ALTA: 'bg-rose-100 text-rose-800 ring-rose-200',
  MEDIA: 'bg-amber-100 text-amber-800 ring-amber-200',
  BAJA: 'bg-sky-100 text-sky-800 ring-sky-200',
}

/** '2026-08-27' -> '27/08/2026', sin pasar por Date para evitar corrimientos de zona. */
function formatearFecha(fecha) {
  if (!fecha) return '—'

  const [anio, mes, dia] = fecha.split('-')

  return `${dia}/${mes}/${anio}`
}
</script>

<template>
  <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
      <thead class="bg-slate-50">
        <tr class="text-left text-xs font-medium uppercase tracking-wide text-slate-500">
          <th scope="col" class="px-4 py-3">Tarea</th>
          <th scope="col" class="px-4 py-3">Estado</th>
          <th scope="col" class="px-4 py-3">Prioridad</th>
          <th scope="col" class="px-4 py-3">Etiquetas</th>
          <th scope="col" class="px-4 py-3 whitespace-nowrap">Vencimiento</th>
          <th scope="col" class="px-4 py-3 text-right">
            <span class="sr-only">Acciones</span>
          </th>
        </tr>
      </thead>

      <tbody class="divide-y divide-slate-100">
        <tr v-for="tarea in tareas" :key="tarea.id" class="align-top hover:bg-slate-50">
          <td class="px-4 py-3">
            <p class="font-medium text-slate-900">{{ tarea.titulo }}</p>
            <p class="mt-0.5 max-w-md text-xs text-slate-500">{{ tarea.descripcion }}</p>
          </td>

          <td class="px-4 py-3">
            <EstadoBadge :estado="tarea.estado" />
          </td>

          <td class="px-4 py-3">
            <span
              class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset"
              :class="PRIORIDADES[tarea.prioridad?.prioridad] ?? 'bg-slate-100 text-slate-700 ring-slate-200'"
            >
              {{ tarea.prioridad?.prioridad ?? '—' }}
            </span>
          </td>

          <td class="px-4 py-3">
            <div v-if="tarea.etiquetas?.length" class="flex flex-wrap gap-1">
              <span
                v-for="etiqueta in tarea.etiquetas"
                :key="etiqueta.id"
                class="inline-flex items-center rounded bg-slate-100 px-1.5 py-0.5 text-xs font-medium text-slate-600"
              >
                {{ etiqueta.etiqueta }}
              </span>
            </div>
            <span v-else class="text-xs text-slate-400">—</span>
          </td>

          <td class="px-4 py-3 whitespace-nowrap text-slate-600">
            {{ formatearFecha(tarea.fecha_vencimiento) }}
          </td>

          <td class="px-4 py-3 text-right whitespace-nowrap">
            <div class="inline-flex gap-1.5">
              <button
                type="button"
                class="rounded-md border border-slate-300 px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-100"
                @click="emit('editar', tarea)"
              >
                Editar
              </button>
              <button
                type="button"
                class="rounded-md border border-rose-200 px-2.5 py-1 text-xs font-medium text-rose-700 hover:bg-rose-50"
                @click="emit('eliminar', tarea)"
              >
                Eliminar
              </button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
