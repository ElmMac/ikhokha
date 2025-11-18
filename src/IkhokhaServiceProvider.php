<?php
namespace Elmmac\Ikhokha;

// use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;
// use Illuminate\Support\Facades\Log;
use Elmmac\Ikhokha\Services\IkhokhaClient;

class IkhokhaServiceProvider extends ServiceProvider
{
    // The application's route middleware. Step 1: This is where we bind services (like HTTP clients) into the container.
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/ikhokha.php',
            'ikhokha'
        );

        // $this->app->singleton(\Elmmac\Ikhokha\Services\IkhokhaClient::class, function ($app) {
        //     return new \Elmmac\Ikhokha\Services\IkhokhaClient(
        //         config('ikhokha')
        //     );
        // });
    }


    // Step 2: If we want to publish config files, routes, views, migrations etc.
    public function boot(): void
    {
        /**
         * -------------------------------------------------------------
         * 1. Merge Config (important: must be called early)
         * -------------------------------------------------------------
         */
        $this->mergeConfigFrom(
            __DIR__ . '/../config/ikhokha.php',
            'ikhokha'
        );

        /**
         * -------------------------------------------------------------
         * 2. Load package routes, views, migrations
         * -------------------------------------------------------------
         */
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');

        // Load views using the namespace "ikhokha::"
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'ikhokha');

        // Load migrations without requiring publish
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        /**
         * -------------------------------------------------------------
         * 3. Publish package assets
         * -------------------------------------------------------------
         */
        // Config publish
        $this->publishes([
            __DIR__ . '/../config/ikhokha.php' => config_path('ikhokha.php'),
        ], 'ikhokha-config');

        // Migrations publish
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'ikhokha-migrations');

        // Views publish
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/ikhokha'),
        ], 'ikhokha-views');
    }

}
