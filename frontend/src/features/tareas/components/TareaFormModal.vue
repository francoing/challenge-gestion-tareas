<!--
  Formulario de alta y edición dentro de un BaseModal.
  Un solo componente para ambos casos: si recibe una tarea por prop, edita; si no, crea.

  Campos: titulo, descripcion, fecha_vencimiento, prioridad_id (select simple),
  etiquetas (selección múltiple).

  Muestra los errores de validación que devuelve Laravel en 422, mapeados por campo.
  Ojo: al crear, el backend NO acepta 'estado' (toda tarea nace pendiente).
-->
