// Composable de la feature tareas: concentra estado y lógica para que
// los componentes sólo rendericen.
//

import { storeToRefs } from 'pinia'
import { onMounted, ref } from 'vue'

import { useCatalogosStore } from '@/stores/catalogos'
import { useNotificacionesStore } from '@/stores/notificaciones'
import { useTareasStore } from '@/stores/tareas'

export function useTareas() {
  const store = useTareasStore()
  const catalogos = useCatalogosStore()
  const avisos = useNotificacionesStore()

  // storeToRefs mantiene la reactividad al desestructurar el state.
  const { tareas, meta, cargando, error } = storeToRefs(store)
  const { prioridades, etiquetas } = storeToRefs(catalogos)

  const formAbierto = ref(false)
  const guardando = ref(false)
  const tareaEnEdicion = ref(null)
  /** Error del formulario, separado del error del listado. */
  const errorForm = ref(null)

  onMounted(() => store.cargar())

  /**
   * Abre el modal. Sin argumento es un alta; con una tarea, una edición.
   *
   * @param {object|null} tarea
   */
  function abrirFormulario(tarea = null) {
    tareaEnEdicion.value = tarea
    errorForm.value = null
    formAbierto.value = true
    // Se piden acá y no al montar: si el usuario nunca abre el formulario,
    // no se gastan dos requests. El store los cachea para la próxima.
    catalogos.cargar()
  }

  function cerrarFormulario() {
    if (guardando.value) return

    formAbierto.value = false
    tareaEnEdicion.value = null
    errorForm.value = null
  }

  /** Tarea pendiente de confirmar borrado. null = sin confirmación abierta. */
  const tareaAEliminar = ref(null)
  const eliminando = ref(false)

  function confirmarEliminacion(tarea) {
    tareaAEliminar.value = tarea
  }

  function cancelarEliminacion() {
    if (eliminando.value) return

    tareaAEliminar.value = null
  }

  /**
   * Borra la tarea confirmada. El error no se propaga: un fallo acá no tiene
   * campos que pintar, y el toast global ya avisó.
   */
  async function eliminarTarea() {
    if (!tareaAEliminar.value) return

    eliminando.value = true

    try {
      await store.eliminar(tareaAEliminar.value.id)
      avisos.exito(`Se eliminó «${tareaAEliminar.value.titulo}».`)
      tareaAEliminar.value = null
    } catch {
      // El listado queda como estaba y el diálogo se cierra igual:
      // insistir con el modal abierto no aporta nada.
      tareaAEliminar.value = null
    } finally {
      eliminando.value = false
    }
  }

  /**
   * Guarda la tarea, creando o actualizando según el modo.
   * Si el backend responde 422, el error queda en errorForm y el modal
   * sigue abierto para que el usuario corrija.
   */
  async function guardarTarea(payload) {
    guardando.value = true
    errorForm.value = null

    try {
      if (tareaEnEdicion.value) {
        await store.actualizar(tareaEnEdicion.value.id, payload)
        avisos.exito('Cambios guardados.')
      } else {
        await store.crear(payload)
        avisos.exito('Tarea creada.')
      }

      formAbierto.value = false
      tareaEnEdicion.value = null
    } catch (e) {
      errorForm.value = e
    } finally {
      guardando.value = false
    }
  }
  /**
   * Cambia de página, siempre respetando límites y evitando efectos secundarios.
   */
  function cambiarPagina(p) {
    if (!meta.value) return
    if (p < 1 || p > meta.value.last_page) return

    store.cargar({ page: p })
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
    // alta y edición
    formAbierto,
    tareaEnEdicion,
    guardando,
    errorForm,
    abrirFormulario,
    cerrarFormulario,
    guardarTarea,
    // eliminación
    tareaAEliminar,
    eliminando,
    confirmarEliminacion,
    cancelarEliminacion,
    eliminarTarea,
    // paginación
    cambiarPagina,
  }
}
