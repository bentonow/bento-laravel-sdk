<?php

namespace Bentonow\BentoLaravel;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Mail;

class BentoLaravelServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Mail::extend('bento', function (array $config = []) {
            return new BentoTransport;
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/bentonow.php',
            'bento'
        );

    }
}