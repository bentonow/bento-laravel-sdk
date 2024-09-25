<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Requests;

use Bentonow\BentoLaravel\DataTransferObjects\CreateTagData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateTag extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly CreateTagData $data
    ) {}

    public function resolveEndpoint(): string
    {
        return '/fetch/tags';
    }

    protected function defaultBody(): array
    {
        return [
            'tag' => [
                'name' => $this->data->name,
            ],
        ];
    }
}
