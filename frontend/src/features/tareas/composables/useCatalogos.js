// Catálogos de prioridades y etiquetas.
//
// No son un store de Pinia porque no hay lógica ni mutaciones: se piden una
// vez y se leen. El estado vive a nivel de módulo, así se comparte entre
// todos los que llamen al composable y el request no se repite.
//
// A diferencia de los estados (common/estados.js), estos NO se pueden fijar
// en el front: prioridad_id y etiquetas[] son claves foráneas, y sus ids los
// asigna la base al sembrar. Cambian de un entorno a otro.

import { ref } from 'vue'

import * as catalogoService from '@/features/tareas/services/catalogoService'

const prioridades = ref([])
const etiquetas = ref([])
const cargando = ref(false)
const cargados = ref(false)

/**
 * Trae ambos catálogos. Es idempotente: si ya están, o si hay un pedido en
 * vuelo, no hace nada.
 */
async function cargar() {
  if (cargados.value || cargando.value) return

  cargando.value = true

  try {
    // En paralelo: son independientes entre sí.
    const [listaPrioridades, listaEtiquetas] = await Promise.all([
      catalogoService.prioridades(),
      catalogoService.etiquetas(),
    ])

    prioridades.value = listaPrioridades
    etiquetas.value = listaEtiquetas
    cargados.value = true
  } finally {
    // Si falló, cargados queda en false y un reintento vuelve a pedirlos.
    cargando.value = false
  }
}

export function useCatalogos() {
  return { prioridades, etiquetas, cargando, cargados, cargar }
}
