<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Requests;

use Bentonow\BentoLaravel\DataTransferObjects\UpdateEmailTemplateData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class UpdateEmailTemplate extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PATCH;

    public function __construct(private readonly UpdateEmailTemplateData $data) {}

    public function resolveEndpoint(): string
    {
        return '/fetch/emails/templates/'.$this->data->id;
    }

    protected function defaultBody(): array
    {
        return [
            'email_template' => array_filter([
                'subject' => $this->data->subject,
                'html' => $this->data->html,
            ]),
        ];
    }
}
