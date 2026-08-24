<?php

namespace Database\Seeders;

use App\Models\Etiqueta;
use App\Models\Prioridad;
use App\Models\Tarea;
use Illuminate\Database\Seeder;

class TareaSeeder extends Seeder
{
    /**
     * Datos de demo escritos a mano en vez de generados: cubren a propósito
     * los tres estados, las tres prioridades, tareas vencidas, próximas a
     * vencer y sin fecha, para que los filtros tengan qué mostrar.
     */
    public function run(): void
    {
        $prioridades = Prioridad::all()->keyBy(fn (Prioridad $p) => $p->prioridad->value);
        $etiquetas = Etiqueta::all()->keyBy(fn (Etiqueta $e) => $e->etiqueta->value);

        foreach ($this->tareas() as $datos) {
            $tarea = Tarea::firstOrCreate(
                ['titulo' => $datos['titulo']],
                [
                    'descripcion' => $datos['descripcion'],
                    'estado' => $datos['estado'],
                    'fecha_vencimiento' => $datos['vence'],
                    'prioridad_id' => $prioridades[$datos['prioridad']]->id,
                ]
            );

            $tarea->etiquetas()->sync(
                collect($datos['etiquetas'])->map(fn (string $e) => $etiquetas[$e]->id)->all()
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function tareas(): array
    {
        return [
            [
                'titulo' => 'Configurar entorno con Docker Compose',
                'descripcion' => 'Levantar los servicios de nginx, php-fpm y MySQL en un solo comando.',
                'estado' => 'completada',
                'vence' => now()->subDays(12)->toDateString(),
                'prioridad' => 'ALTA',
                'etiquetas' => ['DEV'],
            ],
            [
                'titulo' => 'Modelar la base de datos',
                'descripcion' => 'Definir migraciones para tareas, prioridades, etiquetas y su tabla pivote.',
                'estado' => 'completada',
                'vence' => now()->subDays(9)->toDateString(),
                'prioridad' => 'ALTA',
                'etiquetas' => ['DEV'],
            ],
            [
                'titulo' => 'Exponer el API REST de tareas',
                'descripcion' => 'Resource controller con las cinco operaciones del CRUD y sus validaciones.',
                'estado' => 'en_progreso',
                'vence' => now()->subDays(2)->toDateString(),
                'prioridad' => 'ALTA',
                'etiquetas' => ['DEV', 'QA'],
            ],
            [
                'titulo' => 'Documentar los endpoints',
                'descripcion' => 'Detallar rutas, parámetros y códigos de respuesta en el README.',
                'estado' => 'pendiente',
                'vence' => now()->addDays(3)->toDateString(),
                'prioridad' => 'MEDIA',
                'etiquetas' => ['DEV'],
            ],
            [
                'titulo' => 'Construir el formulario de alta',
                'descripcion' => 'Permitir asignar una prioridad y varias etiquetas al crear una tarea.',
                'estado' => 'en_progreso',
                'vence' => now()->addDays(5)->toDateString(),
                'prioridad' => 'ALTA',
                'etiquetas' => ['DEV'],
            ],
            [
                'titulo' => 'Implementar filtros del listado',
                'descripcion' => 'Filtrar por estado, prioridad y rango de fecha de vencimiento.',
                'estado' => 'pendiente',
                'vence' => now()->addDays(7)->toDateString(),
                'prioridad' => 'MEDIA',
                'etiquetas' => ['DEV', 'QA'],
            ],
            [
                'titulo' => 'Escribir pruebas de integración del API',
                'descripcion' => 'Cubrir el CRUD completo, los errores de validación y los filtros.',
                'estado' => 'pendiente',
                'vence' => now()->addDays(10)->toDateString(),
                'prioridad' => 'ALTA',
                'etiquetas' => ['QA'],
            ],
            [
                'titulo' => 'Revisar accesibilidad de la interfaz',
                'descripcion' => 'Contraste de colores, foco visible y navegación por teclado.',
                'estado' => 'pendiente',
                'vence' => now()->addDays(15)->toDateString(),
                'prioridad' => 'BAJA',
                'etiquetas' => ['QA'],
            ],
            [
                'titulo' => 'Preparar la demo para el equipo',
                'descripcion' => 'Guion de presentación y datos de prueba cargados.',
                'estado' => 'pendiente',
                'vence' => now()->addDays(20)->toDateString(),
                'prioridad' => 'MEDIA',
                'etiquetas' => ['RRHH'],
            ],
            [
                'titulo' => 'Coordinar entrevistas técnicas',
                'descripcion' => 'Agendar con los candidatos de la última convocatoria.',
                'estado' => 'en_progreso',
                'vence' => now()->addDays(4)->toDateString(),
                'prioridad' => 'MEDIA',
                'etiquetas' => ['RRHH'],
            ],
            [
                'titulo' => 'Actualizar el manual de onboarding',
                'descripcion' => 'Incorporar los pasos de instalación del nuevo entorno.',
                'estado' => 'pendiente',
                'vence' => null,
                'prioridad' => 'BAJA',
                'etiquetas' => ['RRHH', 'DEV'],
            ],
            [
                'titulo' => 'Depurar dependencias sin uso',
                'descripcion' => 'Revisar composer.json y package.json antes del release.',
                'estado' => 'pendiente',
                'vence' => null,
                'prioridad' => 'BAJA',
                'etiquetas' => [],
            ],
        ];
    }
}
