<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Requests;

use Illuminate\Support\Collection;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateEvents extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /* @var Collection $eventsCollection non-empty-Collection<EventData> */
    public function __construct(private readonly Collection $eventsCollection)
    {
    }

    public function resolveEndpoint(): string
    {
        return 'batch/events';
    }

    protected function defaultBody(): array
    {
        return [
            'events' => $this->eventsCollection->toArray(),
        ];
    }
}
