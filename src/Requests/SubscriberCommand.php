<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class SubscriberCommand extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /* @var array $commands non-empty-array<CommandData> */
    public function __construct(private readonly array $commands) {}

    public function resolveEndpoint(): string
    {
        return '/fetch/commands';
    }

    protected function defaultBody(): array
    {
        return [
            'command' => $this->commands,
        ];
    }
}
