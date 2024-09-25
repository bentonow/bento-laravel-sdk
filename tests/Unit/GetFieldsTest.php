<?php

use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\Requests\GetFields;
use Saloon\Exceptions\Request\Statuses\InternalServerErrorException;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('can get a list of fields', function () {
    $mockClient = new MockClient([
        GetFields::class => MockResponse::make(body: [
            'data' => [
                [
                    'id' => '1234',
                    'type' => 'visitors-fields',
                    'attributes' => [
                        'name' => 'Currency',
                        'key' => 'currency',
                        'whitelisted' => null,
                        'created_at' => '2024-09-12T07:21:33.102Z',
                    ],
                ],
                [
                    'id' => '1235',
                    'type' => 'visitors-fields',
                    'attributes' => [
                        'name' => 'Lifetime Value',
                        'key' => 'lifetime_value',
                        'whitelisted' => null,
                        'created_at' => '2024-09-12T07:21:33.095Z',
                    ],
                ],
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $request = new GetFields;

    $response = $connector->send($request);
    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data'))->toBeArray()->not()->toBeEmpty()->toHaveCount(2)
        ->and($response->json('data')[0]['attributes'])->toBeArray()->toHaveCount(4)
        ->and($response->json('data')[1]['attributes'])->toBeArray()->toHaveCount(4)
        ->and($response->json('data')[0]['id'])->toBeString()->toBe('1234')
        ->and($response->json('data')[1]['id'])->toBeString()->toBe('1235')
        ->and($response->json('data')[0]['attributes']['name'])->toBeString()->toBe('Currency')
        ->and($response->json('data')[1]['attributes']['name'])->toBeString()->toBe('Lifetime Value');

});

it('can get a list of fields no results', function () {
    $mockClient = new MockClient([
        GetFields::class => MockResponse::make(body: [
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $request = new GetFields;

    $response = $connector->send($request);
    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data'))->toBeEmpty();

});

it('can get a list of fields no results (500)', function () {
    $mockClient = new MockClient([
        GetFields::class => MockResponse::make(body: [
        ], status: 500),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $request = new GetFields;

    $response = $connector->send($request);
    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(500)
        ->and($response->json('data'))->toBeArray()->toBeEmpty();

})->throws(InternalServerErrorException::class);
