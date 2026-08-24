// Llamadas HTTP del recurso tareas. Usa la instancia de api/http.js.
// Esta capa NO conoce Pinia ni componentes: solo pide y devuelve datos.
//
// Endpoints del backend:
//   listar(filtros)     GET    /tareas        (estado, prioridad_id, vence_desde,
//                                              vence_hasta, buscar, per_page)
//   ver(id)             GET    /tareas/{id}
//   crear(payload)      POST   /tareas        -> 201
//   actualizar(id, p)   PUT    /tareas/{id}
//   cambiarEstado(id,e) PATCH  /tareas/{id}   (parcial, solo { estado })
//   eliminar(id)        DELETE /tareas/{id}   -> 204 sin body
