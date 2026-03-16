<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Actions;

use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\Requests\ImportSubscribers;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

class UserImportAction
{
    public function execute(LazyCollection|Collection $users): array
    {
        $success = 0;
        $failures = 0;

        if ($users instanceof Collection) {
            $users = LazyCollection::make($users);
        }
        $users->chunk(500)->each(function ($usersChunk) use (&$success, &$failures): void {
            $bento = new BentoConnector;
            $request = new ImportSubscribers($usersChunk->values());
            $importResult = $bento->send($request);
            $success += $importResult->json()['results'] ?? 0;
            $failures += $importResult->json()['failed'] ?? 0;
        });

        return ['results' => $success, 'failed' => $failures];
    }
}
