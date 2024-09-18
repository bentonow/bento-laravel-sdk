<?php

namespace Bentonow\BentoLaravel\Requests;

use Bentonow\BentoLaravel\DataTransferObjects\CreateFieldData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;
class CreateField extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(
        private readonly CreateFieldData $data
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/fetch/fields';
    }

    protected function defaultBody(): array
    {
        return [
            'field' => [
                'key' => $this->data->key,
            ]
        ];
    }

}