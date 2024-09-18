<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Requests;

use Bentonow\BentoLaravel\DataTransferObjects\BlacklistStatusData;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetBlacklistStatus extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly BlacklistStatusData $data
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/experimental/blacklist.json';
    }

    protected function defaultQuery(): array
    {
        return [
            'domain' => $this->data->domain,
            'ip' => $this->data->ipAddress,
        ];
    }

}
