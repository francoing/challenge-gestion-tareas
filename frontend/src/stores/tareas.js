// Store Pinia de tareas (el "store global" que pide el enunciado).
//
// state:   tareas[], meta (paginación de Laravel), cargando, error, filtros
// getters: tareasPorEstado, hayFiltrosActivos
// actions: cargar, crear, actualizar, cambiarEstado, eliminar
//          -> todas delegan en features/tareas/services/tareaService.js
