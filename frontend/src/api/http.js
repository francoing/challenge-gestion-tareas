import axios from 'axios'

import { ApiError } from './ApiError'

/**
 * Instancia única de axios contra el API de Laravel.
 *
 * El header Accept es obligatorio: sin él, Laravel devuelve HTML en los
 * errores en lugar de JSON y el interceptor no podría leer el mensaje.
 */
const http = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? 'http://127.0.0.1:8000/api',
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
  timeout: 10000,
})

// ── Manejo de errores global ──────────────────────────────────────────────
//
// http.js no importa el store de UI a propósito: eso crearía un ciclo
// (store -> service -> http -> store). En su lugar App.vue se suscribe con
// alFallar() y decide qué hacer, normalmente mostrar un toast.

/** @type {Set<(error: ApiError) => void>} */
const suscriptores = new Set()

/**
 * Registra un manejador para los errores que no son de validación.
 * Los 422 quedan afuera: los pinta el formulario, no un toast global.
 *
 * @param   {(error: ApiError) => void} manejador
 * @returns {() => void} función para desuscribirse
 */
export function alFallar(manejador) {
  suscriptores.add(manejador)

  return () => suscriptores.delete(manejador)
}

/**
 * Traduce cualquier fallo de axios a un ApiError con un mensaje en español
 * listo para mostrar.
 *
 * @param   {import('axios').AxiosError} error
 * @returns {ApiError}
 */
function normalizar(error) {
  // Sin response: el request nunca llegó o no volvió.
  if (!error.response) {
    const esTimeout = error.code === 'ECONNABORTED'

    return new ApiError({
      status: 0,
      message: esTimeout
        ? 'El servidor tardó demasiado en responder. Intentá de nuevo.'
        : 'No se pudo conectar con el servidor. Verificá que el backend esté levantado.',
    })
  }

  const { status, data } = error.response

  if (status === 422) {
    return new ApiError({
      status,
      message: data?.message ?? 'Revisá los datos del formulario.',
      errors: data?.errors ?? {},
    })
  }

  if (status >= 500) {
    return new ApiError({
      status,
      // El detalle real queda en los logs del backend, no en pantalla.
      message: 'Ocurrió un error en el servidor. Probá de nuevo en unos minutos.',
    })
  }

  return new ApiError({
    status,
    message: data?.message ?? 'Ocurrió un error inesperado.',
  })
}

http.interceptors.response.use(
  (respuesta) => respuesta,
  (error) => {
    const apiError = normalizar(error)

    if (!apiError.esValidacion) {
      suscriptores.forEach((manejador) => manejador(apiError))
    }

    // Se rechaza igual para que el llamador pueda reaccionar
    // (cortar un loading, mantener el modal abierto, etc.).
    return Promise.reject(apiError)
  },
)

export default http
