// Composable de la feature tareas: concentra estado y lógica para que
// los componentes sólo rendericen.
//
// Por ahora cubre el listado; los handlers de crear/editar/eliminar
// se suman en su paso.

import { storeToRefs } from 'pinia'
import { onMounted } from 'vue'

import { useTareasStore } from '@/stores/tareas'

export function useTareas() {
  const store = useTareasStore()

  // storeToRefs mantiene la reactividad al desestructurar el state.
  const { tareas, meta, cargando, error } = storeToRefs(store)

  onMounted(() => store.cargar())

  return {
    tareas,
    meta,
    cargando,
    error,
    recargar: () => store.cargar(),
  }
}
