<?php

use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\Requests\GetSequences;
use Saloon\Exceptions\Request\Statuses\InternalServerErrorException;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('can get a list of sequences', function () {
    $mockClient = new MockClient([
        GetSequences::class => MockResponse::make(body: [
            'data' => [
                [
                    'id' => '1',
                    'type' => 'sequences',
                    'attributes' => [
                        'name' => 'Onboarding',
                        'created_at' => '2024-01-01T00:00:00.000Z',
                        'email_templates' => [
                            ['id' => 1, 'subject' => 'Welcome!'],
                        ],
                    ],
                ],
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $request = new GetSequences;
    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data'))->toBeArray()->toHaveCount(1)
        ->and($response->json('data')[0]['attributes']['name'])->toBe('Onboarding');
});

it('can get sequences with pagination', function () {
    $mockClient = new MockClient([
        GetSequences::class => MockResponse::make(body: ['data' => []], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $request = new GetSequences(page: 2);
    $response = $connector->send($request);

    expect($response->status())->toBe(200);
});

it('handles empty sequences response', function () {
    $mockClient = new MockClient([
        GetSequences::class => MockResponse::make(body: [], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $request = new GetSequences;
    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data'))->toBeEmpty();
});

it('throws on 500 for sequences', function () {
    $mockClient = new MockClient([
        GetSequences::class => MockResponse::make(body: [], status: 500),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $request = new GetSequences;
    $connector->send($request);
})->throws(InternalServerErrorException::class);
