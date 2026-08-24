<!--
  Vista principal (la que apunta el router en '/').
  Es el único componente de la feature que llama a useTareas():
  toma de ahí datos y handlers, y los reparte a TareaFiltros, TareaTabla,
  TareaFormModal y Paginador.

  Los hijos quedan "tontos" (props + emits), que es lo que los hace reutilizables.
-->
