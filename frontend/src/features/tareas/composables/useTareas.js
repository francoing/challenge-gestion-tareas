// Composable de la feature tareas: concentra estado y lógica para que
// los componentes sólo rendericen.
//
// Por ahora cubre el listado y el alta; editar y eliminar se suman en su paso.

import { storeToRefs } from 'pinia'
import { onMounted, ref } from 'vue'

import { useCatalogosStore } from '@/stores/catalogos'
import { useTareasStore } from '@/stores/tareas'

export function useTareas() {
  const store = useTareasStore()
  const catalogos = useCatalogosStore()

  // storeToRefs mantiene la reactividad al desestructurar el state.
  const { tareas, meta, cargando, error } = storeToRefs(store)
  const { prioridades, etiquetas } = storeToRefs(catalogos)

  const formAbierto = ref(false)
  const guardando = ref(false)
  /** Error del formulario, separado del error del listado. */
  const errorForm = ref(null)

  onMounted(() => store.cargar())

  function abrirFormulario() {
    errorForm.value = null
    formAbierto.value = true
    // Se piden acá y no al montar: si el usuario nunca crea una tarea,
    // no se gastan dos requests. El store los cachea para la próxima.
    catalogos.cargar()
  }

  function cerrarFormulario() {
    if (guardando.value) return

    formAbierto.value = false
    errorForm.value = null
  }

  /**
   * Guarda la tarea. Si el backend responde 422, el error queda en errorForm
   * y el modal sigue abierto para que el usuario corrija.
   */
  async function guardarTarea(payload) {
    guardando.value = true
    errorForm.value = null

    try {
      await store.crear(payload)
      formAbierto.value = false
    } catch (e) {
      errorForm.value = e
    } finally {
      guardando.value = false
    }
  }

  return {
    // listado
    tareas,
    meta,
    cargando,
    error,
    recargar: () => store.cargar(),
    // catálogos
    prioridades,
    etiquetas,
    // alta
    formAbierto,
    guardando,
    errorForm,
    abrirFormulario,
    cerrarFormulario,
    guardarTarea,
  }
}
