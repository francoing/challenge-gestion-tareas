// Instancia única de axios contra el API de Laravel.
// Equivalente a aceConfig.js de Front-ACE.
//
// Va acá:
//   - baseURL desde import.meta.env.VITE_API_URL
//   - header Accept: application/json (si no, Laravel devuelve HTML en los errores)
//   - interceptor de respuesta que centraliza el manejo de errores:
//       422 -> devolver error.response.data.errors al formulario
//       404 -> mensaje "Recurso no encontrado."
//       500 -> notificación genérica
