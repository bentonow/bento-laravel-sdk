<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Requests;

use Bentonow\BentoLaravel\DataTransferObjects\CreateSequenceEmailData;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateSequenceEmail extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function __construct(private readonly CreateSequenceEmailData $data) {}

    public function resolveEndpoint(): string
    {
        return '/fetch/sequences/'.$this->data->sequenceId.'/emails/templates';
    }

    protected function defaultBody(): array
    {
        return [
            'email_template' => array_filter([
                'subject' => $this->data->subject,
                'html' => $this->data->html,
                'inbox_snippet' => $this->data->inboxSnippet,
                'delay_interval' => $this->data->delayInterval,
                'delay_interval_count' => $this->data->delayIntervalCount,
                'editor_choice' => $this->data->editorChoice,
                'cc' => $this->data->cc,
                'bcc' => $this->data->bcc,
                'to' => $this->data->to,
            ], fn ($value) => $value !== null),
        ];
    }
}
