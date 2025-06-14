<?php

namespace ElFarmawy\VdoCipher\Providers;

use Illuminate\Support\ServiceProvider;
use ElFarmawy\VdoCipher\Contracts\VdoCipherInterface;
use ElFarmawy\VdoCipher\Services\VdoCipherService;

class VdoCipherServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/vdocipher.php',
            'vdocipher'
        );

        // Register the service as a singleton
        $this->app->singleton('vdocipher', function ($app) {
            return new VdoCipherService();
        });

        // Register interface binding
        $this->app->bind(VdoCipherInterface::class, function ($app) {
            return $app->make('vdocipher');
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../../config/vdocipher.php' => config_path('vdocipher.php'),
        ], 'config');
    }
}
