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
        $users = User::select('name', 'email')->get();
        $users->chunk(500)->each(function ($chunk): void {
            $importResult = (new UserImportAction)->execute(
                $chunk->map(function ($user) {
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

                })
            );
            $this->success = +$importResult['results'];
            $this->failures = +$importResult['failed'];
        });

        $this->info("Successfully imported {$this->success} users. Failed to import {$this->failures} users.");
        return self::SUCCESS;
    }
}
