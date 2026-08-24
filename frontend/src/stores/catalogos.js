// Store Pinia de catálogos: prioridades y etiquetas.
//
// Son datos fijos (BAJA/MEDIA/ALTA y DEV/QA/RRHH) que alimentan los selects
// del formulario. Se cargan una sola vez y se cachean: si ya están en el
// state, cargar() no vuelve a pegarle al API.
