<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Requests;

use Bentonow\BentoLaravel\DataTransferObjects\GenderData;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetGender extends Request
{
    protected Method $method = Method::POST;

    public function __construct(
        private readonly GenderData $data
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/experimental/gender';
    }

    protected function defaultQuery(): array
    {
        return [
            'name' => $this->data->fullName,
        ];
    }

}
