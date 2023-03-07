<?php

namespace CustomD\SeedOnce;

use CustomD\SeedOnce\Commands\MarkSeeded;
use CustomD\SeedOnce\Commands\Status;
use CustomD\SeedOnce\Repositories\DatabaseSeederRepository;
use CustomD\SeedOnce\Repositories\SeederRepositoryInterface;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class SeedOnceServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/seedonce.php', 'seedonce'
        );

        $this->registerRepository();
    }

    /**
     * Register the seeder repository service.
     *
     * @return void
     */
    protected function registerRepository()
    {
        $this->app->singleton(SeederRepositoryInterface::class, function ($app) {
            $table = $app['config']['seedonce.table'];

            return new DatabaseSeederRepository($app['db'], $table);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (function_exists('config_path')) {
            $this->publishes([
                __DIR__.'/../config/seedonce.php' => config_path('seedonce.php'),
            ], 'laravel-seedonce-config');

            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                MarkSeeded::class,
                Status::class,
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [SeederRepositoryInterface::class];
    }
}
