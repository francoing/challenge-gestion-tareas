// Store Pinia de catálogos: prioridades y etiquetas.
//
// Son datos fijos (BAJA/MEDIA/ALTA y DEV/QA/RRHH) que alimentan los selects
// del formulario. Se cargan una sola vez y se cachean: si ya están en el
// state, cargar() no vuelve a pegarle al API.

import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

import * as catalogoService from '@/features/tareas/services/catalogoService'

export const useCatalogosStore = defineStore('catalogos', () => {
  const prioridades = ref([])
  const etiquetas = ref([])
  const cargando = ref(false)
  const cargados = ref(false)

  const listos = computed(() => cargados.value && !cargando.value)

  /**
   * Trae ambos catálogos. Es idempotente: llamarlo de nuevo no repite el
   * pedido, y si hay uno en vuelo espera a ese en lugar de duplicarlo.
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

  return { prioridades, etiquetas, cargando, cargados, listos, cargar }
})
