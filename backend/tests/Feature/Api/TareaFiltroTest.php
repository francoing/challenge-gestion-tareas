<?php

namespace Tests\Feature\Api;

use App\Enums\EstadoTarea;
use App\Models\Prioridad;
use App\Models\Tarea;
use Database\Seeders\EtiquetaSeeder;
use Database\Seeders\PrioridadSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Filtros del listado (el bonus del enunciado), validados por IndexTareaRequest
 * y aplicados con when() en TareaService::listar().
 */
class TareaFiltroTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PrioridadSeeder::class, EtiquetaSeeder::class]);
    }

    public function test_filtra_por_estado(): void
    {
        Tarea::factory()->count(3)->create(['estado' => EstadoTarea::PENDIENTE]);
        Tarea::factory()->count(2)->create(['estado' => EstadoTarea::COMPLETADA]);

        $respuesta = $this->getJson('/api/tareas?estado=pendiente')->assertOk();

        $respuesta->assertJsonCount(3, 'data');

        foreach ($respuesta->json('data') as $tarea) {
            $this->assertSame('pendiente', $tarea['estado']);
        }
    }

    public function test_filtra_por_prioridad(): void
    {
        $alta = Prioridad::query()->where('prioridad', 'ALTA')->firstOrFail();
        $baja = Prioridad::query()->where('prioridad', 'BAJA')->firstOrFail();

        Tarea::factory()->count(2)->create(['prioridad_id' => $alta->id]);
        Tarea::factory()->count(4)->create(['prioridad_id' => $baja->id]);

        $this->getJson("/api/tareas?prioridad_id={$alta->id}")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_filtra_por_rango_de_fechas_de_vencimiento(): void
    {
        Tarea::factory()->create(['fecha_vencimiento' => '2026-01-15']);
        Tarea::factory()->create(['fecha_vencimiento' => '2026-06-15']);
        Tarea::factory()->create(['fecha_vencimiento' => '2026-12-15']);

        $this->getJson('/api/tareas?vence_desde=2026-06-01&vence_hasta=2026-06-30')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.fecha_vencimiento', '2026-06-15');
    }

    public function test_el_rango_de_fechas_incluye_los_extremos(): void
    {
        Tarea::factory()->create(['fecha_vencimiento' => '2026-06-01']);
        Tarea::factory()->create(['fecha_vencimiento' => '2026-06-30']);

        $this->getJson('/api/tareas?vence_desde=2026-06-01&vence_hasta=2026-06-30')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_vence_desde_funciona_sin_vence_hasta(): void
    {
        Tarea::factory()->create(['fecha_vencimiento' => '2026-01-01']);
        Tarea::factory()->create(['fecha_vencimiento' => '2026-12-01']);

        $this->getJson('/api/tareas?vence_desde=2026-06-01')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_busca_en_el_titulo(): void
    {
        Tarea::factory()->create(['titulo' => 'Configurar Docker Compose']);
        Tarea::factory()->create(['titulo' => 'Maquetar la tabla']);

        $this->getJson('/api/tareas?buscar=Docker')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.titulo', 'Configurar Docker Compose');
    }

    public function test_busca_tambien_en_la_descripcion(): void
    {
        Tarea::factory()->create([
            'titulo' => 'Levantar el entorno',
            'descripcion' => 'Usar Docker para php-fpm, nginx y MySQL.',
        ]);
        Tarea::factory()->create([
            'titulo' => 'Otra tarea',
            'descripcion' => 'Sin relación con el término buscado.',
        ]);

        $this->getJson('/api/tareas?buscar=Docker')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    /**
     * El OR de título/descripción va agrupado en un closure. Sin ese paréntesis
     * se mezclaría con los demás filtros y ampliaría el resultado.
     */
    public function test_la_busqueda_no_desarma_los_otros_filtros(): void
    {
        Tarea::factory()->create([
            'titulo' => 'Docker pendiente',
            'estado' => EstadoTarea::PENDIENTE,
        ]);
        Tarea::factory()->create([
            'titulo' => 'Docker completada',
            'estado' => EstadoTarea::COMPLETADA,
        ]);

        $this->getJson('/api/tareas?buscar=Docker&estado=pendiente')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.titulo', 'Docker pendiente');
    }

    public function test_combina_estado_y_prioridad(): void
    {
        $alta = Prioridad::query()->where('prioridad', 'ALTA')->firstOrFail();

        Tarea::factory()->create(['estado' => EstadoTarea::PENDIENTE, 'prioridad_id' => $alta->id]);
        Tarea::factory()->create(['estado' => EstadoTarea::COMPLETADA, 'prioridad_id' => $alta->id]);
        Tarea::factory()->create(['estado' => EstadoTarea::PENDIENTE, 'prioridad_id' => Prioridad::query()->where('prioridad', 'BAJA')->value('id')]);

        $this->getJson("/api/tareas?estado=pendiente&prioridad_id={$alta->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_respeta_el_per_page(): void
    {
        Tarea::factory()->count(10)->create();

        $this->getJson('/api/tareas?per_page=4')
            ->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonPath('meta.per_page', 4)
            ->assertJsonPath('meta.total', 10);
    }

    public function test_sin_filtros_devuelve_todas_las_tareas(): void
    {
        Tarea::factory()->count(5)->create();

        $this->getJson('/api/tareas')
            ->assertOk()
            ->assertJsonPath('meta.total', 5);
    }

    public function test_un_filtro_sin_coincidencias_devuelve_lista_vacia(): void
    {
        Tarea::factory()->count(3)->create(['estado' => EstadoTarea::PENDIENTE]);

        $this->getJson('/api/tareas?estado=completada')
            ->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.total', 0);
    }
}
