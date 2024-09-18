<?php

namespace Bentonow\BentoLaravel;

use Bentonow\BentoLaravel\Responses\BentoApiResponse;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Connector;

class BentoConnector extends Connector
{
    protected ?string $response = BentoApiResponse::class;

    public function resolveBaseUrl(): string
    {
        return 'https://app.bentonow.com/api/v1';
    }

    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    protected function defaultQuery(): array
    {
        return [
            'site_uuid' => config('bentonow.siteUUID'),
        ];
    }

    protected function defaultAuth(): BasicAuthenticator
    {
        return new BasicAuthenticator(config('bentonow.publishableKey'), config('bentonow.secretKey'));
    }
}
