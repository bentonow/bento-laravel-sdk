<?php

namespace Bentonow\BentoLaravel;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    protected $description = 'Install the Bentonow configuration file.';

    protected $name = 'bento:install';

    public function handle(): int
    {
        $this->callSilently('vendor:publish', [
            '--tag' => 'bento-config',
        ]);

        if ($this->confirm('Would you like to star the repo on GitHub?')) {
            $url = 'https://github.com/bentonow/bento-laravel-sdk';

            $command = [
                'Darwin' => 'open',
                'Linux' => 'xdg-open',
                'Windows' => 'start',
            ][PHP_OS_FAMILY] ?? null;

            if ($command) {
                exec("{$command} {$url}");
            }
        }

        $this->info('Ledger has been installed!');

        return self::SUCCESS;
    }
}
