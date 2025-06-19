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

            $response = Http::baseUrl(sprintf('https://%s', self::HOST))
                ->withQueryParameters([
                    'site_uuid' => config('bentonow.site_uuid', config('bentonow.siteUUID')),
                ])
                ->withBasicAuth(
                    config('bentonow.publishable_key', config('bentonow.publishableKey')),
                    config('bentonow.secret_key', config('bentonow.secretKey')),
                )
                ->post('/api/v1/batch/emails', $bodyParameters);

            // Check for authorization errors
            if ($response->status() === 401) {
                throw new TransportException(
                    'BentoTransport: Authorization failed (401) - Please check your BENTO_PUBLISHABLE_KEY and BENTO_SECRET_KEY in your .env file.',
                    401
                );
            }

            // Check for 500 error with specific error message
            if ($response->status() === 500) {
                $body = $response->body();
                if (strpos($body, 'Author not authorized to send on this account') !== false) {
                    throw new TransportException(
                        'BentoTransport: Bento Author not authorized to send on this account (500) - Check your Bento Authors update your env and try again.',
                        500
                    );
                }
            }

            $response->throw();
        } catch (ConnectionException|RequestException $e) {
            if ($e instanceof RequestException && $e->response?->status() === 401) {
                throw new TransportException(
                    'BentoTransport: Authorization failed (401) - Please check your BENTO_PUBLISHABLE_KEY and BENTO_SECRET_KEY in your .env file.',
                    401,
                    $e
                );
            }
            throw new TransportException('Failed to send email via BentoTransport: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function __toString(): string
    {
        return 'bento';
    }
}
