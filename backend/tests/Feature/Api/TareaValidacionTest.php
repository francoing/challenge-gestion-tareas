<?php

namespace Tests\Feature\Api;

use App\Models\Etiqueta;
use App\Models\Prioridad;
use App\Models\Tarea;
use Database\Seeders\EtiquetaSeeder;
use Database\Seeders\PrioridadSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Validaciones de los FormRequest y manejo global de errores
 * configurado en bootstrap/app.php.
 */
class TareaValidacionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PrioridadSeeder::class, EtiquetaSeeder::class]);
    }

    // ── Crear ───────────────────────────────────────────────────────────

    public function test_requiere_titulo_descripcion_y_prioridad(): void
    {
        $this->postJson('/api/tareas', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['titulo', 'descripcion', 'prioridad_id']);
    }

    public function test_los_mensajes_de_error_estan_en_espanol(): void
    {
        $this->postJson('/api/tareas', [])
            ->assertUnprocessable()
            ->assertJsonPath('errors.titulo.0', 'El título es obligatorio.')
            ->assertJsonPath('errors.descripcion.0', 'La descripción es obligatoria.')
            ->assertJsonPath('errors.prioridad_id.0', 'Debe seleccionar una prioridad.');
    }

    public function test_rechaza_un_titulo_de_mas_de_255_caracteres(): void
    {
        $this->postJson('/api/tareas', [
            'titulo' => str_repeat('a', 256),
            'descripcion' => 'Descripción válida.',
            'prioridad_id' => Prioridad::query()->value('id'),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('titulo');
    }

    public function test_rechaza_una_prioridad_inexistente(): void
    {
        $this->postJson('/api/tareas', [
            'titulo' => 'Tarea',
            'descripcion' => 'Descripción.',
            'prioridad_id' => 999,
        ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.prioridad_id.0', 'La prioridad seleccionada no existe.');
    }

    public function test_rechaza_etiquetas_duplicadas(): void
    {
        $id = Etiqueta::query()->value('id');

        $this->postJson('/api/tareas', [
            'titulo' => 'Tarea',
            'descripcion' => 'Descripción.',
            'prioridad_id' => Prioridad::query()->value('id'),
            'etiquetas' => [$id, $id],
        ])
            ->assertUnprocessable()
            ->assertJsonFragment(['No se puede repetir la misma etiqueta.']);
    }

    public function test_rechaza_una_etiqueta_inexistente(): void
    {
        $this->postJson('/api/tareas', [
            'titulo' => 'Tarea',
            'descripcion' => 'Descripción.',
            'prioridad_id' => Prioridad::query()->value('id'),
            'etiquetas' => [999],
        ])
            ->assertUnprocessable()
            ->assertJsonFragment(['Alguna de las etiquetas seleccionadas no existe.']);
    }

    public function test_rechaza_una_fecha_de_vencimiento_invalida(): void
    {
        $this->postJson('/api/tareas', [
            'titulo' => 'Tarea',
            'descripcion' => 'Descripción.',
            'fecha_vencimiento' => 'no-es-una-fecha',
            'prioridad_id' => Prioridad::query()->value('id'),
        ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.fecha_vencimiento.0', 'La fecha de vencimiento no es una fecha válida.');
    }

    public function test_no_persiste_nada_cuando_la_validacion_falla(): void
    {
        $this->postJson('/api/tareas', ['titulo' => 'Incompleta'])
            ->assertUnprocessable();

        $this->assertDatabaseCount('tareas', 0);
    }

    // ── Actualizar ──────────────────────────────────────────────────────

    public function test_rechaza_un_estado_invalido_al_actualizar(): void
    {
        $tarea = Tarea::factory()->create();

        $this->patchJson("/api/tareas/{$tarea->id}", ['estado' => 'inventado'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.estado.0', 'El estado debe ser pendiente, en_progreso o completada.');
    }

    public function test_rechaza_un_titulo_vacio_al_actualizar(): void
    {
        $tarea = Tarea::factory()->create();

        $this->patchJson("/api/tareas/{$tarea->id}", ['titulo' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('titulo');
    }

    // ── Filtros del listado ─────────────────────────────────────────────

    public function test_rechaza_un_estado_invalido_al_filtrar(): void
    {
        $this->getJson('/api/tareas?estado=inventado')
            ->assertUnprocessable()
            ->assertJsonPath('errors.estado.0', 'El estado debe ser pendiente, en_progreso o completada.');
    }

    public function test_rechaza_un_rango_de_fechas_invertido(): void
    {
        $this->getJson('/api/tareas?vence_desde=2026-12-31&vence_hasta=2026-01-01')
            ->assertUnprocessable()
            ->assertJsonPath('errors.vence_hasta.0', 'La fecha hasta no puede ser anterior a la fecha desde.');
    }

    public function test_rechaza_un_per_page_mayor_al_maximo(): void
    {
        $this->getJson('/api/tareas?per_page=500')
            ->assertUnprocessable()
            ->assertJsonPath('errors.per_page.0', 'No se pueden pedir más de 100 tareas por página.');
    }

    public function test_rechaza_una_prioridad_inexistente_al_filtrar(): void
    {
        $this->getJson('/api/tareas?prioridad_id=999')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('prioridad_id');
    }

    // ── Manejo global de errores ────────────────────────────────────────

    public function test_devuelve_404_json_para_una_tarea_inexistente(): void
    {
        $this->getJson('/api/tareas/999999')
            ->assertNotFound()
            ->assertExactJson(['message' => 'Recurso no encontrado.']);
    }

    /**
     * Laravel expondría "No query results for model [App\Models\Tarea] 999999".
     * El handler de bootstrap/app.php lo reemplaza por un mensaje genérico.
     */
    public function test_el_404_no_expone_el_namespace_del_modelo(): void
    {
        $respuesta = $this->getJson('/api/tareas/999999')->assertNotFound();

        $this->assertStringNotContainsString('App\\Models', $respuesta->getContent());
        $this->assertStringNotContainsString('Tarea', $respuesta->getContent());
    }

    /**
     * El whereNumber('tarea') de la ruta evita que un id no numérico llegue
     * al controller y reviente al castear a int.
     */
    public function test_un_id_no_numerico_devuelve_404_y_no_500(): void
    {
        $this->getJson('/api/tareas/abc')->assertNotFound();
    }

    public function test_devuelve_404_al_actualizar_una_tarea_inexistente(): void
    {
        $this->patchJson('/api/tareas/999999', ['estado' => 'completada'])
            ->assertNotFound();
    }

    public function test_devuelve_404_al_eliminar_una_tarea_inexistente(): void
    {
        $this->deleteJson('/api/tareas/999999')->assertNotFound();
    }
}
