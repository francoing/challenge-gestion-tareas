/**
 * Error normalizado del API.
 *
 * Axios devuelve formas muy distintas según dónde falle: un 422 de Laravel,
 * un 500 con HTML, o un fallo de red sin response. Esta clase las unifica en
 * una sola forma para que stores y componentes no tengan que inspeccionar
 * error.response?.data?.errors en cada llamada.
 */
export class ApiError extends Error {
  /**
   * @param {object}  params
   * @param {number}  params.status   Código HTTP. 0 si no hubo respuesta (red caída).
   * @param {string}  params.message  Mensaje listo para mostrarle al usuario.
   * @param {object}  params.errors   Errores por campo de Laravel: { titulo: ['...'] }
   */
  constructor({ status, message, errors = {} }) {
    super(message)
    this.name = 'ApiError'
    this.status = status
    this.errors = errors
  }

  /** 422: el formulario debe pintar los mensajes campo por campo. */
  get esValidacion() {
    return this.status === 422
  }

  get esNoEncontrado() {
    return this.status === 404
  }

  get esDeServidor() {
    return this.status >= 500
  }

  /** Sin respuesta del servidor: backend caído, CORS o sin conexión. */
  get esDeRed() {
    return this.status === 0
  }

  /**
   * Primer mensaje de error de un campo, o null si ese campo no falló.
   * Pensado para usarse directo en el template del formulario.
   *
   * @param   {string} campo
   * @returns {string|null}
   */
  errorDe(campo) {
    return this.errors[campo]?.[0] ?? null
  }
}
