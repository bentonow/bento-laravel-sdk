<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Actions;

use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\Requests\ImportSubscribers;
use Illuminate\Support\Collection;

class UserImportAction
{
    private int $success = 0;

    private int $failures = 0;

    public function execute(Collection $users): array
    {

        $users->chunk(500)->each(function ($usersChunk): void {
            $bento = new BentoConnector;
            $request = new ImportSubscribers($usersChunk);
            $importResult = $bento->send($request);
            $this->success = +$importResult->json()['results'];
            $this->failures = +$importResult->json()['failed'];
        });

        return ['results' => $this->success, 'failed' => $this->failures];

    }
}
