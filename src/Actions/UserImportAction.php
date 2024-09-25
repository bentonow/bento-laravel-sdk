<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Actions;

use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\Requests\ImportSubscribers;
use Illuminate\Support\Collection;

class UserImportAction
{
    public function execute(Collection $users): void
    {

        $users->chunk(500)->each(function ($usersChunk): void {
            $bento = new BentoConnector;
            $request = new ImportSubscribers($usersChunk);
            $bento->send($request);
        });

    }
}
