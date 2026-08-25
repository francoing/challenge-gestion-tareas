// Estados de una tarea. Espejan el ENUM de la migración, que es cerrado:
// no hay endpoint de catálogo porque los valores no cambian sin un deploy.
//
// Vive acá y no dentro del badge para que el formulario y la insignia
// usen los mismos textos y no se desincronicen.

export const ESTADOS = [
  { valor: 'pendiente', texto: 'Pendiente', clases: 'bg-slate-100 text-slate-700 ring-slate-200' },
  { valor: 'en_progreso', texto: 'En progreso', clases: 'bg-amber-100 text-amber-800 ring-amber-200' },
  { valor: 'completada', texto: 'Completada', clases: 'bg-emerald-100 text-emerald-800 ring-emerald-200' },
]

/** Búsqueda por valor, con un fallback que muestra el crudo si el backend suma uno nuevo. */
export function buscarEstado(valor) {
  return (
    ESTADOS.find((estado) => estado.valor === valor) ?? {
      valor,
      texto: valor,
      clases: 'bg-slate-100 text-slate-700 ring-slate-200',
    }
  )
}
