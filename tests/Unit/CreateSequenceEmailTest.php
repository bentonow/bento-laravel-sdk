<?php

use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\DataTransferObjects\CreateSequenceEmailData;
use Bentonow\BentoLaravel\Requests\CreateSequenceEmail;
use Saloon\Exceptions\Request\Statuses\InternalServerErrorException;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('can create a sequence email with required fields only', function () {
    $mockClient = new MockClient([
        CreateSequenceEmail::class => MockResponse::make(body: [
            'data' => [
                'id' => '1',
                'type' => 'email_templates',
                'attributes' => [
                    'subject' => 'Welcome!',
                    'html' => '<p>Hello</p>',
                ],
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = new CreateSequenceEmailData(
        sequenceId: '123',
        subject: 'Welcome!',
        html: '<p>Hello</p>',
    );
    $request = new CreateSequenceEmail($data);
    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($request->body()->get('email_template'))->toBe([
            'subject' => 'Welcome!',
            'html' => '<p>Hello</p>',
        ]);
});

it('can create a sequence email with all fields', function () {
    $mockClient = new MockClient([
        CreateSequenceEmail::class => MockResponse::make(body: [
            'data' => [
                'id' => '1',
                'type' => 'email_templates',
                'attributes' => [
                    'subject' => 'Follow Up',
                    'html' => '<p>Hi again</p>',
                ],
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = new CreateSequenceEmailData(
        sequenceId: '123',
        subject: 'Follow Up',
        html: '<p>Hi again</p>',
        inboxSnippet: 'Preview text',
        delayInterval: 'days',
        delayIntervalCount: 3,
        editorChoice: 'html',
        cc: 'cc@example.com',
        bcc: 'bcc@example.com',
        to: '{{ email }}',
    );
    $request = new CreateSequenceEmail($data);
    $response = $connector->send($request);

    expect($response->status())->toBe(200)
        ->and($request->body()->get('email_template'))->toBe([
            'subject' => 'Follow Up',
            'html' => '<p>Hi again</p>',
            'inbox_snippet' => 'Preview text',
            'delay_interval' => 'days',
            'delay_interval_count' => 3,
            'editor_choice' => 'html',
            'cc' => 'cc@example.com',
            'bcc' => 'bcc@example.com',
            'to' => '{{ email }}',
        ]);
});

it('throws on 500 for create sequence email', function () {
    $mockClient = new MockClient([
        CreateSequenceEmail::class => MockResponse::make(body: [], status: 500),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = new CreateSequenceEmailData(
        sequenceId: '123',
        subject: 'Test',
        html: '<p>Test</p>',
    );
    $request = new CreateSequenceEmail($data);
    $connector->send($request);
})->throws(InternalServerErrorException::class);
