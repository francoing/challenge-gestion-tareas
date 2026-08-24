// Store Pinia de tareas (el "store global" que pide el enunciado).
//
// Guarda el listado, la paginación y el estado de carga. Las llamadas HTTP
// las hace tareaService: el store no sabe que existe axios.

import { defineStore } from 'pinia'
import { ref } from 'vue'

import * as tareaService from '@/features/tareas/services/tareaService'

export const useTareasStore = defineStore('tareas', () => {
  const tareas = ref([])
  const meta = ref(null)
  const cargando = ref(false)
  /** @type {import('vue').Ref<import('@/api/ApiError').ApiError|null>} */
  const error = ref(null)

  /** Últimos filtros usados, para poder recargar con el mismo criterio. */
  const ultimosFiltros = ref({})

  /**
   * Trae el listado desde el API.
   *
   * @param {object} filtros
   */
  async function cargar(filtros = ultimosFiltros.value) {
    ultimosFiltros.value = filtros
    cargando.value = true
    error.value = null

    try {
      const respuesta = await tareaService.listar(filtros)
      tareas.value = respuesta.tareas
      meta.value = respuesta.meta
    } catch (e) {
      // El toast global ya lo disparó el interceptor de http.js.
      // Acá sólo se guarda para que la vista pueda ofrecer "reintentar".
      error.value = e
      tareas.value = []
      meta.value = null
    } finally {
      cargando.value = false
    }
  }

  /**
   * Crea una tarea y recarga el listado.
   *
   * A diferencia de cargar(), acá el error NO se atrapa: el formulario
   * necesita recibir el 422 para pintar los mensajes campo por campo.
   *
   * Se recarga en vez de insertar la tarea en el array porque el orden lo
   * define el backend (por fecha de vencimiento, las sin fecha al final):
   * agregarla al principio la mostraría en una posición que no le toca.
   *
   * @param   {object} payload
   * @returns {Promise<object>} la tarea creada
   */
  async function crear(payload) {
    const tarea = await tareaService.crear(payload)

    await cargar()

    return tarea
  }

  return { tareas, meta, cargando, error, ultimosFiltros, cargar, crear }
})
