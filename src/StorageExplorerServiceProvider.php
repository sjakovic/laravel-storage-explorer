<?php

namespace Jakovic\StorageExplorer;

use Illuminate\Support\ServiceProvider;
use Jakovic\StorageExplorer\Services\StorageService;

class StorageExplorerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/storage-explorer.php', 'storage-explorer');

        $this->app->singleton(StorageService::class, function ($app) {
            return new StorageService(
                config('storage-explorer.disk', 'local'),
                config('storage-explorer.root_path', ''),
            );
        });
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'storage-explorer');

        if (config('storage-explorer.standalone.enabled', true)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/storage-explorer.php' => config_path('storage-explorer.php'),
            ], 'storage-explorer-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/storage-explorer'),
            ], 'storage-explorer-views');
        }
    }
}
