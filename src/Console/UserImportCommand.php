<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Console;

use Illuminate\Console\Command;

class UserImportCommand extends Command
{
    protected $description = 'Import Users from database.';

    protected $name = 'bento:import-users';

    public function handle(): int
    {

        return self::SUCCESS;
    }
}
