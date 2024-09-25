<?php

use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\DataTransferObjects\BlacklistStatusData;
use Bentonow\BentoLaravel\Requests\GetBlacklistStatus;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('can check blacklist by ip address', function () {
    $mockClient = new MockClient([
        GetBlacklistStatus::class => MockResponse::make(body: [
            'data' => [
                'query' => '1.1.1.1',
                'description' => 'If any of the following blacklist providers contains true you have a problem on your hand.',
                'results' => false,
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = new BlacklistStatusData(domain: null, ipAddress: '1.1.1.1');

    $request = new GetBlacklistStatus($data);

    $response = $connector->send($request);
    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data')['query'])->toBe('1.1.1.1')
        ->and($response->json('data')['description'])->toBe('If any of the following blacklist providers contains true you have a problem on your hand.')
        ->and($response->json('data')['results'])->toBe(false)
        ->and($request->query()->get('ip'))->not()->toBeEmpty()->toBe('1.1.1.1');
});

it('can check blacklist by domain', function () {
    $mockClient = new MockClient([
        GetBlacklistStatus::class => MockResponse::make(body: [
            'data' => [
                'query' => 'bentonow.com',
                'description' => 'If any of the following blacklist providers contains true you have a problem on your hand.',
                'results' => false,
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = new BlacklistStatusData(domain: 'bentonow.com', ipAddress: null);

    $request = new GetBlacklistStatus($data);

    $response = $connector->send($request);
    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data')['query'])->toBe('bentonow.com')
        ->and($response->json('data')['description'])->toBe('If any of the following blacklist providers contains true you have a problem on your hand.')
        ->and($response->json('data')['results'])->toBe(false)
        ->and($request->query()->get('domain'))->not()->toBeEmpty()->toBe('bentonow.com');
});

it('can not check blacklist', function () {
    $mockClient = new MockClient([
        GetBlacklistStatus::class => MockResponse::make(body: [
            'data' => [
                'query' => null,
                'description' => 'If any of the following blacklist providers contains true you have a problem on your hand.',
                'results' => [
                    'result' => 'Please provide an IP or clean domain (google.com).',
                ],
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = new BlacklistStatusData(domain: null, ipAddress: null);

    $request = new GetBlacklistStatus($data);

    $response = $connector->send($request);
    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data')['query'])->toBe(null)
        ->and($response->json('data')['description'])->toBe('If any of the following blacklist providers contains true you have a problem on your hand.')
        ->and($response->json('data')['results']['result'])->toBe('Please provide an IP or clean domain (google.com).')
        ->and($request->query()->get('domain'))->toBeNull()
        ->and($request->query()->get('ip'))->toBeNull();

});
