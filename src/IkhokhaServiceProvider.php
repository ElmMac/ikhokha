<?php
namespace Elmmac\Ikhokha;

use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

class IkhokhaServiceProvider extends ServiceProvider
{
    // The application's route middleware. Step 1: This is where we bind services (like HTTP clients) into the container.
    public function register()
    {
        // Merge the Config |So Laravel can access your config with config('ikhokha.client_id') etc.
        $this->mergeConfigFrom(
            __DIR__ . '/../config/ikhokha.php',
            'ikhokha'
        );

    }

    // Step 2: If we want to publish config files, routes, views, migrations etc.
    public function boot()
    {
        // Migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        // Routes
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php'); // only if used
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'ikhokha');
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/ikhokha'),
        ], 'ikhokha-views');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations'); // if any
        $this->mergeConfigFrom(__DIR__ . '/../config/ikhokha.php', 'ikhokha');

        // Log::info("Ikhokha package is loaded!");
        // Update config file
        $this->publishes([
            __DIR__ . '/../config/ikhokha.php' => config_path('ikhokha.php')
        ], 'ikhokha-config');

    }
}
