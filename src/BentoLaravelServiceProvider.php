<?php

namespace Bentonow\BentoLaravel;

use Bentonow\BentoLaravel\Console\InstallCommand;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

class BentoLaravelServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/bentonow.php' => config_path('bentonow.php'),
            ], 'bentonow');
        }

        Mail::extend('bento', function (array $config = []) {
            return new BentoTransport;
        });
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/bentonow.php',
            'bentonow'
        );
    }
}
