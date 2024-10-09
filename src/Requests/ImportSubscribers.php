<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Requests;

use Illuminate\Support\Collection;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class ImportSubscribers extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /* @var Collection $subscriberCollection non-empty-Collection<ImportSubscribersData> */
    public function __construct(private readonly Collection $subscriberCollection) {}

    public function resolveEndpoint(): string
    {
        return '/batch/subscribers';
    }

    protected function defaultBody(): array
    {
        return [
            'subscribers' => $this->subscriberCollection->map(fn ($subscriber) => $subscriber->__toArray()),
        ];
    }
}
