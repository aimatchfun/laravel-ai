<?php

namespace Daavelar\LaravelAI\Providers;

use Daavelar\LaravelAI\Services\AIService;
use Illuminate\Support\ServiceProvider;

class AIServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/ai.php', 'ai'
        );

        $this->app->singleton('ai', function ($app) {
            return new AIService($app);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/ai.php' => config_path('ai.php'),
        ], 'config');
    }
}