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

    public function handle(): int
    {
        $users = User::select('name', 'email')->get();
        $users->chunk(500)->each(function ($chunk): void {
            (UserImportAction::class)->execute(
                $chunk->map(function ($user) {
                    return [
                        new ImportSubscribersData(
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
                        ),
                    ];
                })
            );
        });

        return self::SUCCESS;
    }
}
