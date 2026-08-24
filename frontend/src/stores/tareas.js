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

  /**
   * Trae el listado desde el API.
   *
   * @param {object} filtros
   */
  async function cargar(filtros = {}) {
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

  return { tareas, meta, cargando, error, cargar }
})
