<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Actions;

use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\Requests\ImportSubscribers;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;

class UserImportAction
{
    private int $success = 0;

    private int $failures = 0;

    public function execute(LazyCollection|Collection $users): array
    {
        if ($users instanceof Collection) {
            $users = LazyCollection::make($users);
        }

        $users->chunk(500)->each(function ($usersChunk): void {
            $bento = new BentoConnector;
            $request = new ImportSubscribers($usersChunk->collect());
            $importResult = $bento->send($request);
            $this->success = +$importResult->json()['results'] ?? 0;
            $this->failures = +$importResult->json()['failed'] ?? 0;
        });

        return ['results' => $this->success, 'failed' => $this->failures];
    }
}
