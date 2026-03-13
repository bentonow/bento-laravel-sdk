<?php

use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\Requests\GetEmailTemplate;
use Saloon\Exceptions\Request\Statuses\InternalServerErrorException;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('can get an email template', function () {
    $mockClient = new MockClient([
        GetEmailTemplate::class => MockResponse::make(body: [
            'data' => [
                'id' => '123',
                'type' => 'email_templates',
                'attributes' => [
                    'subject' => 'Welcome!',
                    'html' => '<p>Hello</p>',
                    'created_at' => '2024-01-01T00:00:00.000Z',
                ],
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $request = new GetEmailTemplate(123);
    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data'))->toBeArray()
        ->and($response->json('data')['attributes']['subject'])->toBe('Welcome!');
});

it('can get an email template with empty response', function () {
    $mockClient = new MockClient([
        GetEmailTemplate::class => MockResponse::make(body: [], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $request = new GetEmailTemplate(999);
    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data'))->toBeEmpty();
});

it('throws on 500 for get email template', function () {
    $mockClient = new MockClient([
        GetEmailTemplate::class => MockResponse::make(body: [], status: 500),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $request = new GetEmailTemplate(123);
    $connector->send($request);
})->throws(InternalServerErrorException::class);
