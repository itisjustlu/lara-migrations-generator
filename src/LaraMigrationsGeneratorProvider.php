<?php

namespace LucasRuroken\LaraMigrationsGenerator;

use Illuminate\Support\ServiceProvider;
use LucasRuroken\LaraMigrationsGenerator\Commands\LaraMigrationsGeneratorCommand;

class LaraMigrationsGeneratorProvider extends ServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton('command.generate:migrations:mysql', function($app) {

            return new LaraMigrationsGeneratorCommand();
        });

        $this->commands('generate:migrations:mysql');
    }
}