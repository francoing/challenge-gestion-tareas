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
   * ¿La página pedida quedó más allá de la última existente?
   *
   * Se exige lista vacía además de comparar los números: con total 0 el
   * backend responde current_page 1 y last_page 1, que no es un error.
   *
   * @param {{tareas: object[], meta: {current_page: number, last_page: number}}} respuesta
   */
  function fueraDeRango({ tareas: lista, meta: paginacion }) {
    return lista.length === 0 && paginacion.current_page > paginacion.last_page
  }

  /**
   * Trae el listado desde el API.
   *
   * @param {object} filtros
   */
  async function cargar(filtros = ultimosFiltros.value) {
    ultimosFiltros.value = { ...ultimosFiltros.value, ...filtros }
    cargando.value = true
    error.value = null

    try {
      const respuesta = await tareaService.listar(ultimosFiltros.value)

      // La página pedida puede haber dejado de existir: pasa al borrar el
      // último ítem de la última página, o al filtrar y reducir el total.
      // El backend no da error, devuelve 200 con la lista vacía, así que
      // hay que detectarlo acá y caer a la última página válida.
      //
      // Recursión acotada a un salto: en la nueva petición current_page
      // pasa a ser last_page, con lo que la condición ya no se cumple.
      if (fueraDeRango(respuesta)) {
        return await cargar({ page: respuesta.meta.last_page })
      }

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

  /**
   * Actualiza una tarea y recarga el listado.
   *
   * Como crear(), deja escapar el error para que el formulario pinte el 422.
   * Se recarga porque editar la fecha de vencimiento cambia la posición de
   * la tarea en el orden que define el backend.
   *
   * @param   {number} id
   * @param   {object} payload
   * @returns {Promise<object>} la tarea actualizada
   */
  async function actualizar(id, payload) {
    const tarea = await tareaService.actualizar(id, payload)

    await cargar()

    return tarea
  }

  /**
   * Elimina una tarea y recarga el listado.
   *
   * Se recarga en vez de sacarla del array para que el total y la
   * paginación queden en lo que dice el backend, no en una cuenta local.
   *
   * @param {number} id
   */
  async function eliminar(id) {
    await tareaService.eliminar(id)

    await cargar()
  }

  return { tareas, meta, cargando, error, ultimosFiltros, cargar, crear, actualizar, eliminar }
})
