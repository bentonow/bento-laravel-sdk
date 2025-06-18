<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Requests;

use Illuminate\Support\Collection;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateBroadcast extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly Collection $broadcastCollection
    ) {}

    public function resolveEndpoint(): string
    {
        return '/batch/broadcasts';
    }

    protected function defaultBody(): array
    {
        return [
            'broadcasts' => $this->broadcastCollection->map(fn ($broadcast) => $broadcast->__toArray())->values(),
        ];
    }
}
