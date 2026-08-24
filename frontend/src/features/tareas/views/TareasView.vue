<!--
  Vista principal (la que apunta el router en '/').
  Es el único componente de la feature que llama a useTareas(): toma de ahí
  datos y handlers, y los reparte a los hijos, que quedan "tontos".
-->
<script setup>
import TareaTabla from '@/features/tareas/components/TareaTabla.vue'
import { useTareas } from '@/features/tareas/composables/useTareas'

const { tareas, meta, cargando, error, recargar } = useTareas()
</script>

<template>
  <section>
    <div class="mb-5 flex items-baseline justify-between">
      <h2 class="text-base font-semibold text-slate-900">Tareas</h2>
      <p v-if="meta" class="text-sm text-slate-500">
        {{ meta.total }} {{ meta.total === 1 ? 'tarea' : 'tareas' }}
      </p>
    </div>

    <!-- Cargando -->
    <p v-if="cargando" class="rounded-lg border border-slate-200 bg-white px-4 py-10 text-center text-sm text-slate-500">
      Cargando tareas…
    </p>

    <!-- Error: el toast global ya avisó, acá se ofrece reintentar -->
    <div v-else-if="error" class="rounded-lg border border-rose-200 bg-rose-50 px-4 py-8 text-center">
      <p class="text-sm text-rose-800">{{ error.message }}</p>
      <button
        type="button"
        class="mt-3 rounded-md bg-rose-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-rose-700"
        @click="recargar"
      >
        Reintentar
      </button>
    </div>

    <!-- Sin resultados -->
    <p
      v-else-if="!tareas.length"
      class="rounded-lg border border-dashed border-slate-300 bg-white px-4 py-10 text-center text-sm text-slate-500"
    >
      Todavía no hay tareas cargadas.
    </p>

    <TareaTabla v-else :tareas="tareas" />
  </section>
</template>
