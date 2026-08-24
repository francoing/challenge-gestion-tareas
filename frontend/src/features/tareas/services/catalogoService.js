// Catálogos que alimentan los selects del formulario de tareas.
//
// El front necesita los ids reales: prioridad_id y etiquetas[] son claves
// foráneas, y sus valores los asigna la base al sembrar. Hardcodear
// "ALTA = 3" rompería apenas cambie el orden del seeder.

import http from '@/api/http'

/** @returns {Promise<Array<{id: number, prioridad: string}>>} */
export async function prioridades() {
  const { data } = await http.get('/prioridades')

  return data.data
}

/** @returns {Promise<Array<{id: number, etiqueta: string}>>} */
export async function etiquetas() {
  const { data } = await http.get('/etiquetas')

  return data.data
}
