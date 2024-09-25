<?php

use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\Requests\GetSiteStats;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('can get site stats', function () {
    $mockClient = new MockClient([
        GetSiteStats::class => MockResponse::make(body: [
            'data' => [
                'user_count' => 8,
                'subscriber_count' => 6,
                'unsubscriber_count' => 2,
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $request = new GetSiteStats;

    $response = $connector->send($request);
    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data')['user_count'])->toBeInt()->toBe(8)
        ->and($response->json('data')['subscriber_count'])->toBeInt()->toBe(6)
        ->and($response->json('data')['unsubscriber_count'])->toBeInt()->toBe(2);
});
