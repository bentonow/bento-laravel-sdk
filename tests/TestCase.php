<?php

namespace Bentonow\BentoLaravel\Tests;

use Bentonow\BentoLaravel\BentoLaravelServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            BentoLaravelServiceProvider::class,
        ];
    }

}