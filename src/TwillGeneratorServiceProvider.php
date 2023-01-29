<?php

namespace Haringsrob\TwillGenerator;

use Haringsrob\TwillGenerator\Commands\GenerateBlocksForFileCommand;
use Illuminate\Support\ServiceProvider;

class TwillGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/twill-generator.php', 'twill-generator');
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__ . '/../config/twill-generator.php' => config_path('twill-generator.php'),
        ], 'twill-generator.config');

        // Registering package commands.
        $this->commands([GenerateBlocksForFileCommand::class]);
    }
}
