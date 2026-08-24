<?php

namespace Tests\Feature\Api;

use App\Enums\EstadoTarea;
use App\Models\Etiqueta;
use App\Models\Prioridad;
use App\Models\Tarea;
use Database\Seeders\EtiquetaSeeder;
use Database\Seeders\PrioridadSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cubre las cinco rutas del Resource Controller que pide el enunciado.
 */
class TareaCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PrioridadSeeder::class, EtiquetaSeeder::class]);
    }

    // ── Listar ──────────────────────────────────────────────────────────

    public function test_lista_las_tareas_con_prioridad_y_etiquetas_embebidas(): void
    {
        $tarea = Tarea::factory()->create();
        $tarea->etiquetas()->sync(Etiqueta::query()->take(2)->pluck('id'));

        $this->getJson('/api/tareas')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonStructure([
                'data' => [[
                    'id', 'titulo', 'descripcion', 'estado', 'fecha_vencimiento',
                    'prioridad_id',
                    'prioridad' => ['id', 'prioridad'],
                    'etiquetas' => [['id', 'etiqueta']],
                    'created_at', 'updated_at',
                ]],
                'meta' => ['current_page', 'per_page', 'total'],
            ])
            ->assertJsonCount(2, 'data.0.etiquetas');
    }

    public function test_el_listado_viene_paginado(): void
    {
        Tarea::factory()->count(20)->create();

        $this->getJson('/api/tareas')
            ->assertOk()
            ->assertJsonCount(15, 'data')          // FiltroTareaData::POR_PAGINA
            ->assertJsonPath('meta.total', 20)
            ->assertJsonPath('meta.per_page', 15);
    }

    public function test_las_tareas_sin_fecha_de_vencimiento_quedan_al_final(): void
    {
        Tarea::factory()->create(['fecha_vencimiento' => null, 'titulo' => 'Sin fecha']);
        Tarea::factory()->create(['fecha_vencimiento' => '2026-01-01', 'titulo' => 'Con fecha']);

        $titulos = array_column($this->getJson('/api/tareas')->json('data'), 'titulo');

        $this->assertSame(['Con fecha', 'Sin fecha'], $titulos);
    }

    // ── Crear ───────────────────────────────────────────────────────────

    public function test_crea_una_tarea_con_prioridad_y_varias_etiquetas(): void
    {
        $prioridad = Prioridad::query()->firstOrFail();
        $etiquetas = Etiqueta::query()->take(2)->pluck('id')->all();

        $respuesta = $this->postJson('/api/tareas', [
            'titulo' => 'Maquetar el listado en Vue',
            'descripcion' => 'Tabla con filtros y badges de prioridad.',
            'fecha_vencimiento' => '2026-09-15',
            'prioridad_id' => $prioridad->id,
            'etiquetas' => $etiquetas,
        ]);

        $respuesta->assertCreated()
            ->assertJsonPath('data.titulo', 'Maquetar el listado en Vue')
            ->assertJsonPath('data.prioridad.id', $prioridad->id)
            ->assertJsonCount(2, 'data.etiquetas');

        $this->assertDatabaseHas('tareas', ['titulo' => 'Maquetar el listado en Vue']);

        foreach ($etiquetas as $etiquetaId) {
            $this->assertDatabaseHas('etiqueta_tarea', [
                'tarea_id' => $respuesta->json('data.id'),
                'etiqueta_id' => $etiquetaId,
            ]);
        }
    }

    /**
     * StoreTareaRequest no acepta 'estado': el DEFAULT de la migración manda.
     */
    public function test_la_tarea_nace_en_estado_pendiente_aunque_se_mande_otro(): void
    {
        $this->postJson('/api/tareas', [
            'titulo' => 'Tarea nueva',
            'descripcion' => 'El estado enviado se ignora.',
            'estado' => 'completada',
            'prioridad_id' => Prioridad::query()->value('id'),
        ])
            ->assertCreated()
            ->assertJsonPath('data.estado', EstadoTarea::PENDIENTE->value);
    }

    public function test_las_etiquetas_son_opcionales_al_crear(): void
    {
        $this->postJson('/api/tareas', [
            'titulo' => 'Tarea sin etiquetas',
            'descripcion' => 'No se envía la clave etiquetas.',
            'prioridad_id' => Prioridad::query()->value('id'),
        ])
            ->assertCreated()
            ->assertJsonCount(0, 'data.etiquetas');
    }

    public function test_la_fecha_de_vencimiento_es_opcional(): void
    {
        $this->postJson('/api/tareas', [
            'titulo' => 'Tarea sin vencimiento',
            'descripcion' => 'fecha_vencimiento es nullable.',
            'fecha_vencimiento' => null,
            'prioridad_id' => Prioridad::query()->value('id'),
        ])
            ->assertCreated()
            ->assertJsonPath('data.fecha_vencimiento', null);
    }

    // ── Ver detalle ─────────────────────────────────────────────────────

    public function test_muestra_una_tarea_especifica(): void
    {
        $tarea = Tarea::factory()->create(['titulo' => 'Tarea buscada']);

        $this->getJson("/api/tareas/{$tarea->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $tarea->id)
            ->assertJsonPath('data.titulo', 'Tarea buscada');
    }

    public function test_la_fecha_se_serializa_como_yyyy_mm_dd(): void
    {
        $tarea = Tarea::factory()->create(['fecha_vencimiento' => '2026-09-15']);

        $this->getJson("/api/tareas/{$tarea->id}")
            ->assertJsonPath('data.fecha_vencimiento', '2026-09-15');
    }

    // ── Actualizar ──────────────────────────────────────────────────────

    public function test_actualiza_todos_los_campos_de_una_tarea(): void
    {
        $tarea = Tarea::factory()->create();
        $otraPrioridad = Prioridad::query()->where('id', '!=', $tarea->prioridad_id)->firstOrFail();

        $this->putJson("/api/tareas/{$tarea->id}", [
            'titulo' => 'Título corregido',
            'descripcion' => 'Descripción corregida.',
            'estado' => EstadoTarea::EN_PROGRESO->value,
            'fecha_vencimiento' => '2026-10-01',
            'prioridad_id' => $otraPrioridad->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.titulo', 'Título corregido')
            ->assertJsonPath('data.estado', 'en_progreso')
            ->assertJsonPath('data.prioridad.id', $otraPrioridad->id);
    }

    /**
     * Todas las reglas de UpdateTareaRequest usan 'sometimes', así que el
     * frontend puede mandar sólo el campo que cambia desde el listado.
     */
    public function test_permite_cambiar_solo_el_estado_sin_pisar_lo_demas(): void
    {
        $tarea = Tarea::factory()->pendiente()->create(['titulo' => 'Título original']);

        $this->patchJson("/api/tareas/{$tarea->id}", [
            'estado' => EstadoTarea::COMPLETADA->value,
        ])
            ->assertOk()
            ->assertJsonPath('data.estado', 'completada')
            ->assertJsonPath('data.titulo', 'Título original')
            ->assertJsonPath('data.prioridad_id', $tarea->prioridad_id);
    }

    public function test_sincroniza_las_etiquetas_en_lugar_de_acumularlas(): void
    {
        $tarea = Tarea::factory()->create();
        $ids = Etiqueta::query()->pluck('id');

        $tarea->etiquetas()->sync([$ids[0], $ids[1]]);

        $this->patchJson("/api/tareas/{$tarea->id}", [
            'etiquetas' => [$ids[2]],
        ])
            ->assertOk()
            ->assertJsonCount(1, 'data.etiquetas')
            ->assertJsonPath('data.etiquetas.0.id', $ids[2]);

        $this->assertDatabaseCount('etiqueta_tarea', 1);
    }

    /**
     * TareaData distingue clave ausente de array vacío:
     * ausente = no tocar, vacío = quitar todas.
     */
    public function test_un_array_vacio_de_etiquetas_las_quita_todas(): void
    {
        $tarea = Tarea::factory()->create();
        $tarea->etiquetas()->sync(Etiqueta::query()->pluck('id'));

        $this->patchJson("/api/tareas/{$tarea->id}", ['etiquetas' => []])
            ->assertOk()
            ->assertJsonCount(0, 'data.etiquetas');

        $this->assertDatabaseCount('etiqueta_tarea', 0);
    }

    public function test_omitir_las_etiquetas_las_deja_intactas(): void
    {
        $tarea = Tarea::factory()->create();
        $tarea->etiquetas()->sync(Etiqueta::query()->take(2)->pluck('id'));

        $this->patchJson("/api/tareas/{$tarea->id}", ['titulo' => 'Sólo cambia el título'])
            ->assertOk()
            ->assertJsonCount(2, 'data.etiquetas');
    }

    // ── Eliminar ────────────────────────────────────────────────────────

    public function test_elimina_una_tarea_y_devuelve_204_sin_body(): void
    {
        $tarea = Tarea::factory()->create();

        $respuesta = $this->deleteJson("/api/tareas/{$tarea->id}");

        $respuesta->assertNoContent();
        $this->assertEmpty($respuesta->getContent());
        $this->assertDatabaseMissing('tareas', ['id' => $tarea->id]);
    }

    /**
     * El pivote se limpia por cascadeOnDelete() de la migración.
     */
    public function test_al_eliminar_limpia_la_tabla_pivote(): void
    {
        $tarea = Tarea::factory()->create();
        $tarea->etiquetas()->sync(Etiqueta::query()->pluck('id'));

        $this->assertDatabaseCount('etiqueta_tarea', 3);

        $this->deleteJson("/api/tareas/{$tarea->id}")->assertNoContent();

        $this->assertDatabaseCount('etiqueta_tarea', 0);
    }

    public function test_las_etiquetas_del_catalogo_sobreviven_al_borrar_la_tarea(): void
    {
        $tarea = Tarea::factory()->create();
        $tarea->etiquetas()->sync(Etiqueta::query()->pluck('id'));

        $this->deleteJson("/api/tareas/{$tarea->id}")->assertNoContent();

        $this->assertDatabaseCount('etiquetas', 3);
    }
}
