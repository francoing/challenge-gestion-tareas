# Gestión de Tareas

API REST en Laravel y SPA en Vue 3 para administrar tareas con prioridad y etiquetas.

**Backend:** Laravel 13 · PHP 8.3 · MySQL 8  
**Frontend:** Vue 3 · Pinia · Vue Router · Vite · Tailwind 4 · Axios

---

## Cómo levantarlo

Único requisito: **Docker Desktop** (en Windows, con WSL 2).

```bash
git clone <url-del-repo>
cd gestion-tareas
docker compose up --build
```

La primera vez tarda unos minutos: instala dependencias, espera a MySQL y corre
migraciones y seeders. No hay que crear el `.env` ni cargar datos a mano.

| | |
|---|---|
| **Aplicación** | <http://localhost:5173> |
| **API** | <http://localhost:8000/api> |
| MySQL | `localhost:3307` · base `gestion_tareas` · `gestion` / `gestion` |

> MySQL usa el puerto **3307** para no chocar con una instalación local.

Los seeders cargan 3 prioridades, 3 etiquetas y 12 tareas de ejemplo.

```bash
docker compose down                                  # bajar
docker compose down -v                               # bajar y borrar la base
docker compose exec backend php artisan test         # correr los tests
```

---

## API

Base: `http://localhost:8000/api`

| Método | Ruta | |
|---|---|---|
| `GET` | `/tareas` | Listado paginado, con filtros |
| `POST` | `/tareas` | Crear |
| `GET` | `/tareas/{id}` | Detalle |
| `PUT` | `/tareas/{id}` | Actualizar completa |
| `PATCH` | `/tareas/{id}` | Actualizar parcial |
| `DELETE` | `/tareas/{id}` | Eliminar |
| `GET` | `/prioridades` · `/etiquetas` | Catálogos |

**Filtros del listado**, todos opcionales y combinables:
`estado`, `prioridad_id`, `vence_desde`, `vence_hasta`, `buscar`, `per_page`, `page`.

```
GET /api/tareas?estado=pendiente&prioridad_id=2&buscar=docker
```

Se validan en `IndexTareaRequest`: un valor inválido devuelve `422`, no se ignora.
Los errores siempre son JSON, con los mensajes en español.

En `postman/` están la colección y el environment, con tests en cada request.

---

## Modelo de datos

- **Prioridad → Tareas** (1:N): una tarea tiene una prioridad.
- **Tareas ↔ Etiquetas** (N:M): por la tabla pivote `etiqueta_tarea`.
- `estado`, `prioridad` y `etiqueta` son `ENUM` en MySQL, con enums de PHP del mismo valor.

---

## Arquitectura

```
FormRequest  →  DTO  →  Controller  →  Interface ─bind─▶ Service  →  Eloquent  →  Resource
   valida        entrada    sólo HTTP        lógica de negocio          contrato JSON
                 tipada                      y transacciones             de salida
```

Cada archivo tiene una sola razón para cambiar, y el controller queda en 56 líneas sin
un solo `if`.

**Por qué no hay repositorios.** Eloquent ya *es* la capa de acceso a datos; un
repositorio encima sería un envoltorio sin comportamiento propio. Se justifica con varias
fuentes de datos o para testear sin base — acá los tests corren contra SQLite en memoria.

**Por qué el controller recibe `int $id` y no el modelo.** Con route model binding la
búsqueda queda fuera del service. Pasando el id, todo lo que toca la base vive en un solo
lugar.

**Por qué los DTO registran qué campos llegaron.** Es lo que hace posible el `PATCH`:
mandar sólo `{"estado": "completada"}` deja el resto intacto, mientras que mandar
`{"fecha_vencimiento": null}` sí la borra. Sin ese registro serían indistinguibles.

**Por qué el estado no se acepta al crear.** Toda tarea nace `pendiente` y sólo se mueve
por `PUT`/`PATCH`. Es una regla de negocio, no una omisión.

Las escrituras van en transacción, porque tocan la tarea y el pivote de etiquetas. Las
relaciones se cargan con `with()` y se exponen con `whenLoaded()` para evitar el N+1.

### Frontend

Organizado por feature (`features/tareas/`), no por tipo de archivo.

- El **store de Pinia** es la fuente de verdad del listado: tareas, paginación, carga,
  error y últimos filtros.
- El **composable `useTareas`** concentra el estado de pantalla y los handlers, para que
  los componentes queden presentacionales.
- Los **errores se manejan en un solo lugar**: un interceptor en `api/http.js` normaliza
  cualquier fallo a un `ApiError` y dispara el toast global. Los `422` quedan excluidos a
  propósito, porque esos los pinta el formulario campo por campo.
- Los **estados están fijos en el front y los catálogos vienen de la API**: `estado` es un
  `ENUM` y el valor *es* el dato, mientras que `prioridad_id` y los ids de etiquetas son
  claves foráneas que asigna la base y cambian entre entornos.

---

## Tests

50 tests de feature, 153 aserciones, sobre SQLite en memoria:

```bash
docker compose exec backend php artisan test
```

Cubren el CRUD completo, cada filtro por separado y combinados, y cada regla de validación
que debe fallar. Pasan por el kernel HTTP completo, así que verifican el contrato que
consume el frontend.

---

## Notas

- **La autenticación no está implementada.** El enunciado la marca opcional; Sanctum está
  instalado pero los endpoints son públicos. Se priorizó terminar bien el alcance
  obligatorio antes que dejar esa cadena a medias.
- **Las credenciales del `docker-compose.yml` son de desarrollo local** y están
  versionadas a propósito, para que el proyecto levante con un solo comando.
