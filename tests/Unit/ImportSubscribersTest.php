<?php

use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\DataTransferObjects\ImportSubscribersData;
use Bentonow\BentoLaravel\Requests\ImportSubscribers;
use Saloon\Exceptions\Request\Statuses\InternalServerErrorException;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('can import subscribers', function () {
    $mockClient = new MockClient([
        ImportSubscribers::class => MockResponse::make(body: [
            'data' => [
                'results' => 2,
                'failed' => 0,
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = collect([
        new ImportSubscribersData(
            email: 'test@example.com',
            firstName: 'John',
            lastName: 'Doe',
            tags: ['onboarding', 'website', 'purchased'],
            removeTags: ['temp_subscriber'],
            fields: ['order_count' => 2, 'lifetime_value' => 80, 'currency' => 'USD']
        ),
        new ImportSubscribersData(
            email: 'test2@example.com',
            firstName: 'Jane',
            lastName: 'Doe',
            tags: ['onboarding', 'mobile', 'purchased'],
            removeTags: ['unverified'],
            fields: ['order_count' => 1, 'lifetime_value' => 1000, 'currency' => 'USD']
        ),
    ]);

    $request = new ImportSubscribers($data);

    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data')['results'])->toBeInt()->toBe(2)
        ->and($response->json('data')['failed'])->toBeInt()->toBe(0)
        ->and($request->body()->get('subscribers'))->not()->toBeEmpty();
});

it('can not import subscribers', function () {
    $mockClient = new MockClient([
        ImportSubscribers::class => MockResponse::make(body: [
            'data' => [
                'results' => 0,
                'failed' => 2,
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = collect([
        new ImportSubscribersData(
            email: 'test@example.com',
            firstName: 'John',
            lastName: 'Doe',
            tags: ['onboarding', 'website', 'purchased'],
            removeTags: ['temp_subscriber'],
            fields: ['order_count' => 2, 'lifetime_value' => 80, 'currency' => 'USD']
        ),
        new ImportSubscribersData(
            email: 'test2@example.com',
            firstName: 'Jane',
            lastName: 'Doe',
            tags: ['onboarding', 'mobile', 'purchased'],
            removeTags: ['unverified'],
            fields: ['order_count' => 1, 'lifetime_value' => 1000, 'currency' => 'USD']
        ),
    ]);

    $request = new ImportSubscribers($data);

    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data')['results'])->toBeInt()->toBe(0)
        ->and($response->json('data')['failed'])->toBeInt()->toBe(2)
        ->and($request->body()->get('subscribers'))->not()->toBeEmpty();
});

it('has an error when import subscribers', function () {
    $mockClient = new MockClient([
        ImportSubscribers::class => MockResponse::make(body: [], status: 500),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = collect([
        new ImportSubscribersData(
            email: 'test@example.com',
            firstName: 'John',
            lastName: 'Doe',
            tags: ['onboarding', 'website', 'purchased'],
            removeTags: ['temp_subscriber'],
            fields: ['order_count' => 2, 'lifetime_value' => 80, 'currency' => 'USD']
        ),
        new ImportSubscribersData(
            email: 'test2@example.com',
            firstName: 'Jane',
            lastName: 'Doe',
            tags: ['onboarding', 'mobile', 'purchased'],
            removeTags: ['unverified'],
            fields: ['order_count' => 1, 'lifetime_value' => 1000, 'currency' => 'USD']
        ),
    ]);

    $request = new ImportSubscribers($data);

    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(500)
        ->and($response->json('data'))->toBeEmpty()
        ->and($request->body()->get('subscribers'))->not()->toBeEmpty();
})->throws(InternalServerErrorException::class);
