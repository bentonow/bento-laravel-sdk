<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Requests;

use Bentonow\BentoLaravel\DataTransferObjects\ValidateEmailData;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class ValidateEmail extends Request
{
    protected Method $method = Method::POST;

    public function __construct(
        private readonly ValidateEmailData $data
    ) {
    }

    public function resolveEndpoint(): string
    {
        return '/experimental/validation';
    }

    protected function defaultQuery(): array
    {
        return [
            'email' => $this->data->emailAddress,
            'name' => $this->data->fullName,
            'user_agent' => $this->data->userAgent,
            'ip' => $this->data->ipAddress,
        ];
    }

}
{

}
