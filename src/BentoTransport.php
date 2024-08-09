<?php

namespace Bentonow\BentoLaravel;

use Illuminate\Support\Facades\Http;
use bentonow\BentoLaravel\MailDetails;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\MessageConverter;
class BentoTransport extends AbstractTransport
{
    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $mailDetails = MailDetails::fromEmail($email);
        Http::baseUrl('https://app.bentonow.com')
            ->withQueryParameters([
                'site_uuid' => config('bentonow.siteUUID'),
            ])
            ->withBasicAuth(
                username: config('bentonow.publishableKey'),
                password: config('bentonow.secretKey')
            )
            ->post('/api/v1/batch/emails', [
                'emails' => [[
                    "to" => $mailDetails->toAddress,
                    "from" => $mailDetails->fromAddress,
                    "subject" => $email->getSubject(),
                    "html_body" => $email->getHtmlBody(),
                    "transactional" => true,
                ]]
            ])
            ->throw();
    }
    public function __toString(): string
    {
        return 'bento';
    }
}