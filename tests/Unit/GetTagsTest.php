<?php

use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\Requests\GetTags;
use Saloon\Exceptions\Request\Statuses\InternalServerErrorException;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('can get a list of tags', function () {
    $mockClient = new MockClient([
        GetTags::class => MockResponse::make(body: [
            'data' => [
                [
                    'id' => '1234',
                    'type' => 'tags',
                    'attributes' => [
                        'name' => 'purchased',
                        'created_at' => '2024-08-19T00:09:08.678Z',
                        'discarded_at' => null,
                        'site_id' => 123,
                    ],
                ],
                [
                    'id' => '1235',
                    'type' => 'tags',
                    'attributes' => [
                        'name' => 'onboarding',
                        'created_at' => '2024-08-19T00:09:08.414Z',
                        'discarded_at' => null,
                        'site_id' => 123,
                    ],
                ],
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $request = new GetTags;

    $response = $connector->send($request);
    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data'))->toBeArray()->not()->toBeEmpty()->toHaveCount(2)
        ->and($response->json('data')[0]['attributes'])->toBeArray()->toHaveCount(4)
        ->and($response->json('data')[1]['attributes'])->toBeArray()->toHaveCount(4)
        ->and($response->json('data')[0]['id'])->toBeString()->toBe('1234')
        ->and($response->json('data')[1]['id'])->toBeString()->toBe('1235')
        ->and($response->json('data')[0]['attributes']['name'])->toBeString()->toBe('purchased')
        ->and($response->json('data')[1]['attributes']['name'])->toBeString()->toBe('onboarding');

});

it('can get a list of tags no results', function () {
    $mockClient = new MockClient([
        GetTags::class => MockResponse::make(body: [
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $request = new GetTags;

    $response = $connector->send($request);
    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data'))->toBeEmpty();

});

it('can get a list of tags no results (500)', function () {
    $mockClient = new MockClient([
        GetTags::class => MockResponse::make(body: [
        ], status: 500),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $request = new GetTags;

    $response = $connector->send($request);
    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(500)
        ->and($response->json('data'))->toBeArray()->toBeEmpty();

})->throws(InternalServerErrorException::class);
