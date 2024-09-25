<?php

use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\DataTransferObjects\SegmentStatsData;
use Bentonow\BentoLaravel\Requests\GetSegmentStats;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('can get segment stats', function () {
    $mockClient = new MockClient([
        GetSegmentStats::class => MockResponse::make(body: [
            'data' => [
                'user_count' => 0,
                'subscriber_count' => 0,
                'unsubscriber_count' => 0,
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = new SegmentStatsData(segment_id: '123');

    $request = new GetSegmentStats($data);

    $response = $connector->send($request);
    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data')['user_count'])->toBeInt()->toBe(0)
        ->and($response->json('data')['subscriber_count'])->toBeInt()->toBe(0)
        ->and($response->json('data')['unsubscriber_count'])->toBeInt()->toBe(0)
        ->and($request->query()->get('segment_id'))->not()->toBeEmpty()->toBe('123');
});
