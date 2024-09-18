<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetTags extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/fetch/tags';
    }
}
