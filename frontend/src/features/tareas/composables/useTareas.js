// Composable de la feature tareas: concentra estado y lógica para que
// los componentes sólo rendericen.
//

import { storeToRefs } from 'pinia'
import { onMounted, ref, watch} from 'vue'

import { useCatalogos } from '@/features/tareas/composables/useCatalogos'
import { useNotificacionesStore } from '@/stores/notificaciones'
import { useTareasStore } from '@/stores/tareas'


export function useTareas() {
  const store = useTareasStore()
  const catalogos = useCatalogos()
  const avisos = useNotificacionesStore()

  // storeToRefs mantiene la reactividad al desestructurar el state.
  const { tareas, meta, cargando, error } = storeToRefs(store)
  const { prioridades, etiquetas } = catalogos

  const formAbierto = ref(false)
  const guardando = ref(false)
  const tareaEnEdicion = ref(null)
  const filtros = ref({
    estado: '',
    prioridad_id: '',
    vence_desde: '',
    vence_hasta: '',
    buscar: '',
  })

  function limpiarFiltros() {
    filtros.value = {
      estado: '',
      prioridad_id: '',
      vence_desde: '',
      vence_hasta: '',
      buscar: '',
    }
  }

  /** Error del formulario, separado del error del listado. */
  const errorForm = ref(null)

  onMounted(() => {
    store.cargar()

    // Los catálogos se piden al montar porque el filtro de prioridad está
    // en pantalla desde el arranque, no sólo dentro del modal.
    //
    // El catch evita un unhandled rejection: a diferencia de store.cargar(),
    // catalogos.cargar() no atrapa sus errores. El toast global ya avisó,
    // y un catálogo vacío no rompe el resto de la pantalla.
    catalogos.cargar()
  })

  /**
   * Abre el modal. Sin argumento es un alta; con una tarea, una edición.
   *
   * @param {object|null} tarea
   */
  function abrirFormulario(tarea = null) {
    tareaEnEdicion.value = tarea
    errorForm.value = null
    formAbierto.value = true

    // Red de seguridad: normalmente ya se cargaron al montar, y el store
    // corta la llamada si los tiene. Sólo hace algo si la carga inicial
    // falló y el usuario abre el formulario después.
    catalogos.cargar().catch(() => {})
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

  async function aplicarFiltros(filtros) {
    await store.cargar(filtros)
  }

  watch(() => filtros.value.estado, () => aplicarFiltros(filtros.value))

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
    // filtros
    filtros,
    aplicarFiltros,
    limpiarFiltros,
  }
}
