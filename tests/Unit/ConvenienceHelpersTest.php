<?php

use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\Requests\CreateEvents;
use Bentonow\BentoLaravel\Requests\FindSubscriber;
use Bentonow\BentoLaravel\Requests\ImportSubscribers;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('can tag a subscriber', function () {
    $mockClient = new MockClient([
        CreateEvents::class => MockResponse::make(body: [
            'results' => 1,
            'failed' => 0,
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $response = $connector->tagSubscriber('user@example.com', 'vip');

    expect($response->status())->toBe(200)
        ->and($response->json('results'))->toBe(1);

    $mockClient->assertSent(CreateEvents::class);
});

it('can add a subscriber', function () {
    $mockClient = new MockClient([
        CreateEvents::class => MockResponse::make(body: [
            'results' => 1,
            'failed' => 0,
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $response = $connector->addSubscriber('user@example.com', ['first_name' => 'John']);

    expect($response->status())->toBe(200)
        ->and($response->json('results'))->toBe(1);

    $mockClient->assertSent(CreateEvents::class);
});

it('can add a subscriber without fields', function () {
    $mockClient = new MockClient([
        CreateEvents::class => MockResponse::make(body: [
            'results' => 1,
            'failed' => 0,
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $response = $connector->addSubscriber('user@example.com');

    expect($response->status())->toBe(200);

    $mockClient->assertSent(CreateEvents::class);
});

it('can remove a subscriber', function () {
    $mockClient = new MockClient([
        CreateEvents::class => MockResponse::make(body: [
            'results' => 1,
            'failed' => 0,
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $response = $connector->removeSubscriber('user@example.com');

    expect($response->status())->toBe(200);

    $mockClient->assertSent(CreateEvents::class);
});

it('can update fields', function () {
    $mockClient = new MockClient([
        CreateEvents::class => MockResponse::make(body: [
            'results' => 1,
            'failed' => 0,
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $response = $connector->updateFields('user@example.com', ['plan' => 'pro']);

    expect($response->status())->toBe(200);

    $mockClient->assertSent(CreateEvents::class);
});

it('can track a purchase', function () {
    $mockClient = new MockClient([
        CreateEvents::class => MockResponse::make(body: [
            'results' => 1,
            'failed' => 0,
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $response = $connector->trackPurchase('user@example.com', [
        'unique' => ['key' => 'order-123'],
        'value' => ['amount' => 9999, 'currency' => 'USD'],
    ]);

    expect($response->status())->toBe(200);

    $mockClient->assertSent(CreateEvents::class);
});

it('can track a custom event', function () {
    $mockClient = new MockClient([
        CreateEvents::class => MockResponse::make(body: [
            'results' => 1,
            'failed' => 0,
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $response = $connector->track(
        'user@example.com',
        '$pageView',
        fields: ['first_name' => 'John'],
        details: ['url' => '/home'],
    );

    expect($response->status())->toBe(200);

    $mockClient->assertSent(CreateEvents::class);
});

it('can remove a tag from a subscriber', function () {
    $mockClient = new MockClient([
        CreateEvents::class => MockResponse::make(body: [
            'results' => 1,
            'failed' => 0,
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $response = $connector->removeTag('user@example.com', 'vip');

    expect($response->status())->toBe(200)
        ->and($response->json('results'))->toBe(1);

    $mockClient->assertSent(CreateEvents::class);
});

it('can upsert a subscriber', function () {
    $mockClient = new MockClient([
        ImportSubscribers::class => MockResponse::make(body: [
            'results' => 1,
            'failed' => 0,
        ], status: 200),
        FindSubscriber::class => MockResponse::make(body: [
            'data' => [
                'id' => '1',
                'type' => 'visitors',
                'attributes' => [
                    'email' => 'user@example.com',
                    'fields' => ['first_name' => 'John'],
                ],
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $response = $connector->upsertSubscriber(
        email: 'user@example.com',
        firstName: 'John',
        tags: ['welcome'],
    );

    expect($response->status())->toBe(200)
        ->and($response->json('data')['attributes']['email'])->toBe('user@example.com');

    $mockClient->assertSent(ImportSubscribers::class);
    $mockClient->assertSent(FindSubscriber::class);
});

it('throws when upsert subscriber import fails', function () {
    $mockClient = new MockClient([
        ImportSubscribers::class => MockResponse::make(body: [
            'results' => 0,
            'failed' => 1,
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $connector->upsertSubscriber(email: 'user@example.com');
})->throws(RuntimeException::class, 'Failed to upsert subscriber [user@example.com]');
