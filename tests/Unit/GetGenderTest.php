<?php

use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\DataTransferObjects\GenderData;
use Bentonow\BentoLaravel\Requests\GetGender;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('it can get the gender for a name', function () {
    $mockClient = new MockClient([
        GetGender::class => MockResponse::make(body: [
            'data' => [
                'gender' => 'male',
                'confidence' => 0.99497487437186,
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = new GenderData('John Doe');

    $request = new GetGender($data);

    $response = $connector->send($request);
    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data')['gender'])->toBeString()->toBe('male')
        ->and($response->json('data')['confidence'])->toBeFloat()->toBe(0.99497487437186)
        ->and($request->query()->get('name'))->not()->toBeEmpty()->toBe('John Doe');
});

it('it failes to get gender for name', function () {
    $mockClient = new MockClient([
        GetGender::class => MockResponse::make(body: [
            'data' => [
                'gender' => 'unknown',
                'confidence' => null,
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = new GenderData('');

    $request = new GetGender($data);

    $response = $connector->send($request);
    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data')['gender'])->toBeString()->toBe('unknown')
        ->and($response->json('data')['confidence'])->toBeNull()
        ->and($request->query()->get('name'))->toBe('');
});
