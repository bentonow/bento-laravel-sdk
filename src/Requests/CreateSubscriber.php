<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Requests;

use Bentonow\BentoLaravel\DataTransferObjects\CreateSubscriberData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateSubscriber extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        protected CreateSubscriberData $subscriber
    ) {}

    public function resolveEndpoint(): string
    {
        return '/fetch/subscribers';
    }

    protected function defaultBody(): array
    {
        return [
            'subscriber' => [
                'email' => $this->subscriber->email,
            ],
        ];
    }
}
