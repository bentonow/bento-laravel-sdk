<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel;

use Bentonow\BentoLaravel\Http\Middleware\BentoSignatureExclusion;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

class BentoLaravelServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/bentonow.php' => config_path('bentonow.php'),
            ], 'bentonow');
        }

        $this->registerCommands();

        // Register the middleware
        $this->app['router']->aliasMiddleware('bento.signature', BentoSignatureExclusion::class);

        Mail::extend('bento', fn (array $config = []) => new BentoTransport);
    }

    /**
     * Register the package's commands.
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\UserImportCommand::class,
                Console\InstallCommand::class,
            ]);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/bentonow.php',
            'bentonow'
        );

        $this->app->singleton('bento', fn ($app) => new BentoConnector);
    }
}
