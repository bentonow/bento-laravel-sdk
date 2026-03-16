<?php

use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\Requests\GetWorkflows;
use Saloon\Exceptions\Request\Statuses\InternalServerErrorException;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('can get a list of workflows', function () {
    $mockClient = new MockClient([
        GetWorkflows::class => MockResponse::make(body: [
            'data' => [
                [
                    'id' => '1',
                    'type' => 'workflows',
                    'attributes' => [
                        'name' => 'Welcome Flow',
                        'created_at' => '2024-01-01T00:00:00.000Z',
                        'email_templates' => [
                            ['id' => 1, 'subject' => 'Welcome!', 'stats' => null],
                        ],
                    ],
                ],
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $request = new GetWorkflows;
    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data'))->toBeArray()->toHaveCount(1)
        ->and($response->json('data')[0]['attributes']['name'])->toBe('Welcome Flow');
});

it('can get workflows with pagination', function () {
    $mockClient = new MockClient([
        GetWorkflows::class => MockResponse::make(body: ['data' => []], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $request = new GetWorkflows(page: 2);
    $response = $connector->send($request);

    expect($response->status())->toBe(200);
});

it('handles empty workflows response', function () {
    $mockClient = new MockClient([
        GetWorkflows::class => MockResponse::make(body: [], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $request = new GetWorkflows;
    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data'))->toBeEmpty();
});

it('throws on 500 for workflows', function () {
    $mockClient = new MockClient([
        GetWorkflows::class => MockResponse::make(body: [], status: 500),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $request = new GetWorkflows;
    $connector->send($request);
})->throws(InternalServerErrorException::class);
