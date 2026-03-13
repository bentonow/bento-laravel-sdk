<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetWorkflows extends Request
{
    protected Method $method = Method::GET;

    public function __construct(private readonly ?int $page = null) {}

    public function resolveEndpoint(): string
    {
        return '/fetch/workflows';
    }

    protected function defaultQuery(): array
    {
        return array_filter([
            'page' => $this->page,
        ]);
    }
}
