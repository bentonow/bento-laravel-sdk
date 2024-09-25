<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Requests;

use Bentonow\BentoLaravel\DataTransferObjects\ContentModerationData;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetContentModeration extends Request
{
    protected Method $method = Method::POST;

    public function __construct(
        private readonly ContentModerationData $data
    ) {}

    public function resolveEndpoint(): string
    {
        return '/experimental/content_moderation';
    }

    protected function defaultQuery(): array
    {
        return [
            'content' => $this->data->content,
        ];
    }
}
