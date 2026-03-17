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
        $fields = array_filter([
            'subject' => $this->data->subject,
            'html' => $this->data->html,
        ], fn ($value) => $value !== null);

        if (empty($fields)) {
            throw new \InvalidArgumentException(
                'At least one of subject or html must be provided to update an email template.'
            );
        }

        return ['email_template' => $fields];
    }
}
