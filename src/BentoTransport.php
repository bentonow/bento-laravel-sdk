<?php

namespace Bentonow\BentoLaravel;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

class BentoTransport extends AbstractTransport
{
    private const HOST = 'app.bentonow.com';

    protected function doSend(SentMessage $message): void
    {
        try {
            $bodyParameters = (new SentMessagePayloadTransformer)
                ->transform($message);

            Http::baseUrl(sprintf('https://%s', self::HOST))
                ->withQueryParameters([
                    'site_uuid' => config('bentonow.site_uuid', config('bentonow.siteUUID')),
                ])
                ->withBasicAuth(
                    config('bentonow.publishable_key', config('bentonow.publishableKey')),
                    config('bentonow.secret_key', config('bentonow.secretKey')),
                )
                ->post('/api/v1/batch/emails', $bodyParameters)
                ->throw()
                ->body();

        } catch (ConnectionException|RequestException $e) {
            throw new TransportException('Failed to send email via BentoTransport', 0, $e);
        }
    }

    public function __toString(): string
    {
        return 'bento';
    }
}
