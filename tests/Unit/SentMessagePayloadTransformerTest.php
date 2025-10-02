<?php

use Bentonow\BentoLaravel\SentMessagePayloadTransformer;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

it('transforms a sent message with full recipient lists into the expected payload', function () {
    $email = (new Email)
        ->from(new Address('sender@example.com', 'Sender Name'))
        ->to(
            new Address('recipient1@example.com', 'Recipient One'),
            new Address('recipient2@example.com')
        )
        ->cc(
            new Address('cc1@example.com'),
            new Address('cc2@example.com', 'CC Two')
        )
        ->bcc(new Address('bcc1@example.com'))
        ->subject('Welcome to Bento')
        ->html('<p>Hello from Bento</p>');

    $sentMessage = new SentMessage(
        $email,
        new Envelope(
            new Address('sender@example.com', 'Sender Name'),
            [
                new Address('recipient1@example.com', 'Recipient One'),
                new Address('recipient2@example.com'),
            ]
        )
    );

    $payload = (new SentMessagePayloadTransformer)->transform($sentMessage);

    expect($payload)->toEqual([
        'emails' => [[
            'from' => 'sender@example.com',
            'subject' => 'Welcome to Bento',
            'html_body' => '<p>Hello from Bento</p>',
            'transactional' => true,
            'to' => 'recipient1@example.com,recipient2@example.com',
            'cc' => 'cc1@example.com,cc2@example.com',
            'bcc' => 'bcc1@example.com',
        ]],
    ]);
});

it('omits optional recipient lists when they are not present on the message', function () {
    $email = (new Email)
        ->from('sender@example.com')
        ->to('recipient@example.com')
        ->subject('Missing optional recipients')
        ->html('<p>Body</p>');

    $sentMessage = new SentMessage(
        $email,
        new Envelope(
            new Address('sender@example.com'),
            [new Address('recipient@example.com')]
        )
    );

    $payload = (new SentMessagePayloadTransformer)->transform($sentMessage);

    expect($payload['emails'][0])->toMatchArray([
        'from' => 'sender@example.com',
        'subject' => 'Missing optional recipients',
        'html_body' => '<p>Body</p>',
        'transactional' => true,
    ]);

    expect(array_key_exists('cc', $payload['emails'][0]))->toBeFalse();
    expect(array_key_exists('bcc', $payload['emails'][0]))->toBeFalse();
});
