<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Requests;

use Bentonow\BentoLaravel\DataTransferObjects\ImportSubscribersData;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class ImportSubscribers extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * @param  Collection<ImportSubscribersData>|LazyCollection<ImportSubscribersData>  $subscriberCollection
     */
    public function __construct(private readonly Collection|LazyCollection $subscriberCollection) {}

    public function resolveEndpoint(): string
    {
        return '/batch/subscribers';
    }

    protected function defaultBody(): array
    {
        return [
            'subscribers' => $this->subscriberCollection->map(fn ($subscriber) => $subscriber->__toArray())->values(),
        ];
    }
}
