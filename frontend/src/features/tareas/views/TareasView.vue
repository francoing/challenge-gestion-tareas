<!--
  Vista principal (la que apunta el router en '/').
  Es el único componente de la feature que llama a useTareas(): toma de ahí
  datos y handlers, y los reparte a los hijos, que quedan "tontos".
-->
<script setup>
import BaseModal from '@/common/components/BaseModal.vue'
import TareaFormModal from '@/features/tareas/components/TareaFormModal.vue'
import TareaTabla from '@/features/tareas/components/TareaTabla.vue'
import { useTareas } from '@/features/tareas/composables/useTareas'
import TareaFiltros from '@/features/tareas/components/TareaFiltros.vue'

const {
  tareas,
  meta,
  cargando,
  error,
  recargar,
  prioridades,
  etiquetas,
  formAbierto,
  tareaEnEdicion,
  guardando,
  errorForm,
  abrirFormulario,
  cerrarFormulario,
  guardarTarea,
  tareaAEliminar,
  eliminando,
  confirmarEliminacion,
  cancelarEliminacion,
  eliminarTarea,
  cambiarPagina,
  filtros,
  limpiarFiltros,
} = useTareas()
</script>

<template>
  <section>
    <div class="mb-4 flex items-baseline gap-3">
      <h2 class="text-base font-semibold text-slate-900">Tareas</h2>
      <p v-if="meta" class="text-sm text-slate-500">
        {{ meta.total }} {{ meta.total === 1 ? 'tarea' : 'tareas' }}
      </p>
    </div>

    <TareaFiltros
      v-model="filtros"
      :prioridades="prioridades"
      :deshabilitado="cargando"
      @limpiar="limpiarFiltros"
    >
      <template #acciones>
        <button
          type="button"
          class="w-full rounded-md bg-slate-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-slate-800"
          @click="abrirFormulario()"
        >
          Nueva tarea
        </button>
      </template>
    </TareaFiltros>

    <!-- Primera carga: todavía no hay nada que mostrar.
         En las recargas (cambio de página, alta, borrado) NO se entra acá:
         la tabla se mantiene en pantalla y sólo se atenúa. -->
    <p
      v-if="cargando && !tareas.length"
      class="rounded-lg border border-slate-200 bg-white px-4 py-10 text-center text-sm text-slate-500"
    >
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

    <!-- Durante una recarga la tabla queda visible pero atenuada: el usuario
         no pierde el contexto y el paginador puede deshabilitarse a la vista.
         aria-busy avisa a los lectores de pantalla que el contenido se está
         actualizando. -->
    <TareaTabla
      v-else
      :tareas="tareas"
      :meta="meta"
      :cargando="cargando"
      :aria-busy="cargando"
      class="transition-opacity duration-200"
      :class="{ 'opacity-50': cargando }"
      @cambiar="cambiarPagina"
      @editar="abrirFormulario"
      @eliminar="confirmarEliminacion"
    />

    <TareaFormModal
      :abierto="formAbierto"
      :tarea="tareaEnEdicion"
      :prioridades="prioridades"
      :etiquetas="etiquetas"
      :guardando="guardando"
      :error="errorForm"
      @cerrar="cerrarFormulario"
      @guardar="guardarTarea"
    />

    <!-- Confirmación de borrado: reusa BaseModal, no necesita componente propio. -->
    <BaseModal
      :abierto="Boolean(tareaAEliminar)"
      titulo="Eliminar tarea"
      :bloqueado="eliminando"
      @cerrar="cancelarEliminacion"
    >
      <p class="text-sm text-slate-600">
        ¿Seguro que querés eliminar
        <span class="font-medium text-slate-900">«{{ tareaAEliminar?.titulo }}»</span>?
        Esta acción no se puede deshacer.
      </p>

      <template #pie>
        <button
          type="button"
          class="rounded-md px-3 py-1.5 text-sm font-medium text-slate-600 hover:bg-slate-100 disabled:opacity-50"
          :disabled="eliminando"
          @click="cancelarEliminacion"
        >
          Cancelar
        </button>
        <button
          type="button"
          class="rounded-md bg-rose-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-rose-700 disabled:opacity-50"
          :disabled="eliminando"
          @click="eliminarTarea"
        >
          {{ eliminando ? 'Eliminando…' : 'Eliminar' }}
        </button>
      </template>
    </BaseModal>
  </section>
</template>
