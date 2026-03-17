<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetFormResponses extends Request
{
    protected Method $method = Method::GET;

    public function __construct(private readonly string $formIdentifier) {}

    public function resolveEndpoint(): string
    {
        return '/fetch/responses';
    }

    protected function defaultQuery(): array
    {
        return [
            'id' => $this->formIdentifier,
        ];
    }
}
