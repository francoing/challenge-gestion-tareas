// Store Pinia de avisos (toasts).
//
// Centraliza los mensajes efímeros de la app. Recibe de dos lados:
//   - errores: el interceptor de api/http.js, enchufado en main.js
//   - éxitos:  las vistas, después de una operación que salió bien
//
// El componente Notificacion.vue sólo lee de acá y renderiza.

import { defineStore } from 'pinia'
import { ref } from 'vue'

const DURACIONES = {
  exito: 3500,
  // Los errores duran más: el usuario tiene que poder leerlos y actuar.
  error: 6000,
}

export const useNotificacionesStore = defineStore('notificaciones', () => {
  /** @type {import('vue').Ref<Array<{id: number, tipo: 'exito'|'error', mensaje: string}>>} */
  const items = ref([])

  let siguienteId = 0
  const temporizadores = new Map()

  function quitar(id) {
    clearTimeout(temporizadores.get(id))
    temporizadores.delete(id)
    items.value = items.value.filter((item) => item.id !== id)
  }

  function programarCierre(id, tipo) {
    clearTimeout(temporizadores.get(id))
    temporizadores.set(id, setTimeout(() => quitar(id), DURACIONES[tipo]))
  }

  /**
   * Agrega un aviso. Si ya hay uno idéntico en pantalla, reinicia su
   * temporizador en vez de apilar otro igual: reintentar tres veces con el
   * backend caído no debería llenar la pantalla de toasts repetidos.
   *
   * @param {'exito'|'error'} tipo
   * @param {string} mensaje
   */
  function agregar(tipo, mensaje) {
    const existente = items.value.find((item) => item.tipo === tipo && item.mensaje === mensaje)

    if (existente) {
      programarCierre(existente.id, tipo)

      return
    }

    const id = siguienteId++
    items.value.push({ id, tipo, mensaje })
    programarCierre(id, tipo)
  }

  const exito = (mensaje) => agregar('exito', mensaje)
  const error = (mensaje) => agregar('error', mensaje)

  return { items, agregar, exito, error, quitar }
})
