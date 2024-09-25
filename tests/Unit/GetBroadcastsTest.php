<?php

use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\Requests\GetBroadcasts;
use Saloon\Exceptions\Request\Statuses\InternalServerErrorException;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('it can get a list of broadcasts', function () {
    $mockClient = new MockClient([
        GetBroadcasts::class => MockResponse::make(body: [
            'data' => [
                [
                    'id' => '1234',
                    'type' => 'visitors-fields',
                    'attributes' => [
                        'name' => 'broadcast 1',
                        'share_url' => 'https://example.com/broadcast/1234',
                        'template' => [
                            'subject' => 'Test Broadcast',
                            'to' => 'test@example.com',
                            'html' => '<h1>Test Broadcast</h1><p>This is a test broadcast.</p>',
                        ],
                        'sent_final_batch_at' => '2024-09-12T07:21:33.102Z',
                        'created_at' => '2024-09-12T07:21:33.102Z',
                        'send_at' => '2024-09-12T07:21:33.102Z',
                        'stats' => [
                            'open_rate' => 0,
                        ],
                    ],
                ],
                [
                    'id' => '1235',
                    'type' => 'visitors-fields',
                    'attributes' => [
                        'name' => 'broadcast 2',
                        'share_url' => 'https://example.com/broadcast/1235',
                        'template' => [
                            'subject' => 'Test Broadcast 2',
                            'to' => 'test@example.com',
                            'html' => '<h1>Test Broadcast 2</h1><p>This is a test broadcast.</p>',
                        ],
                        'sent_final_batch_at' => '2024-09-12T07:21:33.102Z',
                        'created_at' => '2024-09-12T07:21:33.102Z',
                        'send_at' => '2024-09-12T07:21:33.102Z',
                        'stats' => [
                            'open_rate' => 0,
                        ],
                    ],
                ],
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $request = new GetBroadcasts;

    $response = $connector->send($request);
    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data'))->toBeArray()->not()->toBeEmpty()->toHaveCount(2)
        ->and($response->json('data')[0]['attributes'])->toBeArray()->toHaveCount(7)
        ->and($response->json('data')[1]['attributes'])->toBeArray()->toHaveCount(7)
        ->and($response->json('data')[0]['attributes']['template'])->toBeArray()->toHaveCount(3)
        ->and($response->json('data')[1]['attributes']['template'])->toBeArray()->toHaveCount(3)
        ->and($response->json('data')[0]['id'])->toBeString()->toBe('1234')
        ->and($response->json('data')[1]['id'])->toBeString()->toBe('1235')
        ->and($response->json('data')[0]['attributes']['name'])->toBeString()->toBe('broadcast 1')
        ->and($response->json('data')[1]['attributes']['name'])->toBeString()->toBe('broadcast 2')
        ->and($response->json('data')[0]['attributes']['template']['subject'])->toBeString()->tobe('Test Broadcast')
        ->and($response->json('data')[1]['attributes']['template']['subject'])->toBeString()->tobe('Test Broadcast 2');
});

it('can get a list of broadcasts no results', function () {
    $mockClient = new MockClient([
        GetBroadcasts::class => MockResponse::make(body: [
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $request = new GetBroadcasts;

    $response = $connector->send($request);
    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data'))->toBeEmpty();

});

it('can get a list of broadcasts no results (500)', function () {
    $mockClient = new MockClient([
        GetBroadcasts::class => MockResponse::make(body: [
        ], status: 500),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $request = new GetBroadcasts;

    $response = $connector->send($request);
    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(500)
        ->and($response->json('data'))->toBeArray()->toBeEmpty();

})->throws(InternalServerErrorException::class);
