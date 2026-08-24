// Composable de la feature tareas. Es el equivalente Vue de useUsuarios.js
// de Front-ACE: concentra estado y lógica para que los componentes solo rendericen.
//
// Va acá:
//   - refs de filtros y página
//   - lecturas del store con storeToRefs (tareas, cargando, error)
//   - handlers: crear, editar, eliminar, cambiarEstado
//   - watch sobre los filtros para recargar el listado
//
// Devuelve un objeto plano con todo lo que el componente necesita.
