<?php

namespace Bentonow\BentoLaravel;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class BentoTransport extends AbstractTransport
{
    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $mailDetails = MailDetails::fromEmail($email);
        try {
            Http::baseUrl('https://app.bentonow.com')
                ->withQueryParameters([
                    'site_uuid' => config('bentonow.site_uuid'),
                ])
                ->withBasicAuth(
                    username: config('bentonow.publishable_key'),
                    password: config('bentonow.secret_key')
                )
                ->post('/api/v1/batch/emails', [
                    'emails' => [[
                        'to' => $mailDetails->toAddress,
                        'from' => $mailDetails->fromAddress,
                        'subject' => $email->getSubject(),
                        'html_body' => $email->getHtmlBody(),
                        'transactional' => true,
                    ]],
                ])
                ->throw();
        } catch (ConnectionException|RequestException $e) {
        }
    }

    public function __toString(): string
    {
        return 'bento';
    }
}
