<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Requests;

use Bentonow\BentoLaravel\Responses\FindSubscriberResponse;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class FindSubscriber extends Request
{
    protected Method $method = Method::GET;

    protected ?string $response = FindSubscriberResponse::class;

    public function __construct(private readonly string $email)
    {
    }

    public function resolveEndpoint(): string
    {
        return '/fetch/subscribers';
    }

    protected function defaultQuery(): array
    {
        return [
            'site_uuid' => config('bentonow.siteUUID'),
            'email' => $this->email,
        ];
    }
}
