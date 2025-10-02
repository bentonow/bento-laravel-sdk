<?php

use Bentonow\BentoLaravel\BentoTransport;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

function makeSentMessage(): SentMessage
{
    $email = (new Email)
        ->from(new Address('sender@example.com', 'Sender Name'))
        ->to(new Address('recipient@example.com', 'Recipient Name'))
        ->subject('Subject Line')
        ->html('<p>Body</p>');

    return new SentMessage(
        $email,
        new Envelope(
            new Address('sender@example.com', 'Sender Name'),
            [new Address('recipient@example.com', 'Recipient Name')]
        )
    );
}

function makeTransport(): BentoTransport
{
    return new class extends BentoTransport
    {
        public function sendMessage(SentMessage $message): void
        {
            $this->doSend($message);
        }
    };
}

it('sends the correct payload and authentication details to the Bento API', function () {
    config([
        'bentonow.publishable_key' => 'pub',
        'bentonow.secret_key' => 'sec',
        'bentonow.site_uuid' => 'site-uuid',
    ]);

    $capturedRequest = null;

    Http::fake(function ($request) use (&$capturedRequest) {
        $capturedRequest = $request;

        return Http::response(['status' => 'ok'], 200);
    });

    makeTransport()->sendMessage(makeSentMessage());

    expect($capturedRequest)->not()->toBeNull();
    expect($capturedRequest->url())
        ->toBe('https://app.bentonow.com/api/v1/batch/emails?site_uuid=site-uuid');

    $payload = json_decode($capturedRequest->body(), true);

    expect($payload)->toEqual([
        'emails' => [[
            'from' => 'sender@example.com',
            'subject' => 'Subject Line',
            'html_body' => '<p>Body</p>',
            'transactional' => true,
            'to' => 'recipient@example.com',
        ]],
    ]);

    $authorization = $capturedRequest->header('Authorization')[0] ?? '';
    expect($authorization)->toBe('Basic '.base64_encode('pub:sec'));
});

it('throws a transport exception when the API returns 401', function () {
    config([
        'bentonow.publishable_key' => 'pub',
        'bentonow.secret_key' => 'sec',
        'bentonow.site_uuid' => 'site-uuid',
    ]);

    Http::fake(fn () => Http::response([], 401));

    expect(fn () => makeTransport()->sendMessage(makeSentMessage()))
        ->toThrow(TransportException::class, 'Authorization failed (401)');
});

it('throws a descriptive transport exception for author authorization failures', function () {
    config([
        'bentonow.publishable_key' => 'pub',
        'bentonow.secret_key' => 'sec',
        'bentonow.site_uuid' => 'site-uuid',
    ]);

    Http::fake(fn () => Http::response('Author not authorized to send on this account', 500));

    expect(fn () => makeTransport()->sendMessage(makeSentMessage()))
        ->toThrow(TransportException::class, 'Bento Author not authorized to send on this account');
});
