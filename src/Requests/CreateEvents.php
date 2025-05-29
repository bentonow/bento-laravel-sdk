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
    public function __construct(private readonly Collection $eventsCollection) {}

    public function resolveEndpoint(): string
    {
        return 'batch/events';
    }

    protected function defaultBody(): array
    {
        $events = $this->eventsCollection->map(function ($event) {
            return [
                'type' => $event->type,
                'email' => $event->email,
                'fields' => empty($event->fields) ? null : $event->fields,
                'details' => empty($event->details) ? null : $event->details,
            ];
        });

        return [
            'events' => $events->values(),
        ];
    }
}
