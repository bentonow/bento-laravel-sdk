<?php

use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\Requests\GetFormResponses;
use Saloon\Exceptions\Request\Statuses\InternalServerErrorException;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('can get form responses', function () {
    $mockClient = new MockClient([
        GetFormResponses::class => MockResponse::make(body: [
            'data' => [
                [
                    'id' => '1',
                    'type' => 'form_responses',
                    'attributes' => [
                        'uuid' => 'abc-123',
                        'data' => [
                            'id' => 'resp-1',
                            'type' => 'form_submission',
                            'ip' => '127.0.0.1',
                            'date' => '2024-01-01T00:00:00.000Z',
                            'fields' => ['name' => 'John'],
                        ],
                    ],
                ],
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $request = new GetFormResponses('my-form');
    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data'))->toBeArray()->toHaveCount(1)
        ->and($response->json('data')[0]['attributes']['uuid'])->toBe('abc-123');
});

it('handles empty form responses', function () {
    $mockClient = new MockClient([
        GetFormResponses::class => MockResponse::make(body: [], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $request = new GetFormResponses('nonexistent');
    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data'))->toBeEmpty();
});

it('throws on 500 for form responses', function () {
    $mockClient = new MockClient([
        GetFormResponses::class => MockResponse::make(body: [], status: 500),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $request = new GetFormResponses('form-1');
    $connector->send($request);
})->throws(InternalServerErrorException::class);
