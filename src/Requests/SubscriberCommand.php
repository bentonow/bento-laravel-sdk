<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Requests;

use Illuminate\Support\Collection;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class SubscriberCommand extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /* @var Collection $commandsCollection non-empty-Collection<EventData> */
    public function __construct(private readonly Collection $commandsCollection) {}

    public function resolveEndpoint(): string
    {
        return '/fetch/commands';
    }

    protected function defaultBody(): array
    {
        return [
            'command' => $this->commandsCollection->values()->toArray(),
        ];
    }
}
