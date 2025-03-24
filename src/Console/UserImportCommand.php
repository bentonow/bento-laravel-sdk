<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Console;

use App\Models\User;
use Bentonow\BentoLaravel\Actions\UserImportAction;
use Bentonow\BentoLaravel\DataTransferObjects\ImportSubscribersData;
use Illuminate\Console\Command;
use Str;

class UserImportCommand extends Command
{
    protected $description = 'Import Users from database.';

    protected $name = 'bento:import-users';

    private int $success = 0;

    private int $failures = 0;

    public function handle(): int
    {
        $totalSuccess = 0;
        $totalFailures = 0;

        User::select('name', 'email')
            ->lazy(500)
            ->chunk(500)
            ->each(function ($chunk) use (&$totalSuccess, &$totalFailures) {
                $subscribers = $chunk->collect()->map(function ($user) {
                    return new ImportSubscribersData(
                        email: $user->email,
                        firstName: Str::of($user->name)
                            ->after('.')
                            ->before(' ')
                            ->__toString(),
                        lastName: Str::of($user->name)
                            ->after(' ')
                            ->__toString(),
                        tags: ['onboarding_complete'],
                        removeTags: null,
                        fields: ['imported_at' => now()]
                    );
                });

                $importResult = (new UserImportAction)->execute($subscribers);
                $totalSuccess += $importResult['results'];
                $totalFailures += $importResult['failed'];

                $this->info("Processed batch: {$importResult['results']} successful, {$importResult['failed']} failed");
            });
        $this->info("Completed! Successfully imported {$totalSuccess} users. Failed to import {$totalFailures} users.");

        return self::SUCCESS;
    }
}
