<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Services\Tarea\Contracts\TareaInterface::class,
            \App\Services\Tarea\TareaService::class
        );

        $this->app->bind(
            \App\Services\Catalogo\Contracts\CatalogoInterface::class,
            \App\Services\Catalogo\CatalogoService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
