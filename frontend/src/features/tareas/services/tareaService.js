// Llamadas HTTP del recurso tareas. Usa la instancia de api/http.js.
// Esta capa NO conoce Pinia ni componentes: solo pide y devuelve datos.
//
// Por ahora sólo el listado; el resto del CRUD se suma en su paso.

import http from '@/api/http'

/**
 * Descarta claves vacías para no mandar `?estado=` al backend.
 * IndexTareaRequest valida `estado` contra el enum, y una cadena vacía
 * daría 422 en lugar de significar "sin filtro".
 */
function limpiar(filtros) {
  return Object.fromEntries(
    Object.entries(filtros).filter(([, valor]) => valor !== null && valor !== undefined && valor !== ''),
  )
}

/**
 * Lista tareas paginadas.
 *
 * @param   {object} filtros  estado, prioridad_id, vence_desde, vence_hasta, buscar, per_page, page
 * @returns {Promise<{tareas: object[], meta: object}>}
 */
export async function listar(filtros = {}) {
  const { data } = await http.get('/tareas', { params: limpiar(filtros) })

  // Laravel envuelve el listado en { data: [...], links, meta }.
  return { tareas: data.data, meta: data.meta }
}
