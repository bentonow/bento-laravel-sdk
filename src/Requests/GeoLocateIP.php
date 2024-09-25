<?php

namespace Bentonow\BentoLaravel\Requests;

use Bentonow\BentoLaravel\DataTransferObjects\GeoLocateIpData;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class GeoLocateIP extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly GeoLocateIpData $data
    ) {}

    public function resolveEndpoint(): string
    {
        return '/experimental/geolocation';
    }

    protected function defaultQuery(): array
    {
        return [
            'ip' => $this->data->ipAddress,
        ];
    }
}
