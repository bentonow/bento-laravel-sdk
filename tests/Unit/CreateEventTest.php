<?php

declare(strict_types=1);

use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\DataTransferObjects\EventData;
use Bentonow\BentoLaravel\Requests\CreateEvents;
use Saloon\Exceptions\Request\Statuses\InternalServerErrorException;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('can create an event', function (): void {
    $mockClient = new MockClient([
        CreateEvents::class => MockResponse::make(body: [
            'results' => 1,
            'failed' => 0,
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = collect([
        new EventData(
            type: '$completed_onboarding',
            email: 'test@example.com',
            fields: [
                'first_name' => 'John',
                'last_name' => 'Doe',
            ],
        ),
    ]);

    $request = new CreateEvents($data);

    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('results'))->toBe(1)
        ->and($response->json('failed'))->toBe(0)
        ->and($request->body()->get('events'))->not()->toBeEmpty()
        ->and($request->body()->get('events')[0]['type'])->toBe('$completed_onboarding')
        ->and($request->body()->get('events')[0]['email'])->toBe('test@example.com')
        ->and($request->body()->get('events')[0]['fields'])->toBe([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
});

it('can create an event with details', function (): void {
    $mockClient = new MockClient([
        CreateEvents::class => MockResponse::make(body: [
            'results' => 1,
            'failed' => 0,
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = collect([
        new EventData(
            type: '$purchase',
            email: 'test@example.com',
            fields: [
                'first_name' => 'John',
                'last_name' => 'Doe',
            ],
            details: [
                'amount' => 99.99,
                'currency' => 'USD',
                'product_id' => '123',
            ],
        ),
    ]);

    $request = new CreateEvents($data);

    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('results'))->toBe(1)
        ->and($response->json('failed'))->toBe(0)
        ->and($request->body()->get('events'))->not()->toBeEmpty()
        ->and($request->body()->get('events')[0]['type'])->toBe('$purchase')
        ->and($request->body()->get('events')[0]['email'])->toBe('test@example.com')
        ->and($request->body()->get('events')[0]['fields'])->toBe([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ])
        ->and($request->body()->get('events')[0]['details'])->toBe([
            'amount' => 99.99,
            'currency' => 'USD',
            'product_id' => '123',
        ]);
});

it('can create multiple events in a single request', function (): void {
    $mockClient = new MockClient([
        CreateEvents::class => MockResponse::make(body: [
            'results' => 2,
            'failed' => 0,
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = collect([
        new EventData(
            type: '$page_view',
            email: 'user1@example.com',
            fields: [
                'first_name' => 'John',
                'last_name' => 'Doe',
            ],
        ),
        new EventData(
            type: '$form_submission',
            email: 'user2@example.com',
            fields: [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
            ],
        ),
    ]);

    $request = new CreateEvents($data);

    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('results'))->toBe(2)
        ->and($response->json('failed'))->toBe(0)
        ->and($request->body()->get('events'))->toHaveCount(2)
        ->and($request->body()->get('events')[0]['type'])->toBe('$page_view')
        ->and($request->body()->get('events')[0]['email'])->toBe('user1@example.com')
        ->and($request->body()->get('events')[1]['type'])->toBe('$form_submission')
        ->and($request->body()->get('events')[1]['email'])->toBe('user2@example.com');
});

it('handles events with empty fields and details', function (): void {
    $mockClient = new MockClient([
        CreateEvents::class => MockResponse::make(body: [
            'results' => 1,
            'failed' => 0,
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = collect([
        new EventData(
            type: '$page_view',
            email: 'test@example.com',
        ),
    ]);

    $request = new CreateEvents($data);

    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('results'))->toBe(1)
        ->and($response->json('failed'))->toBe(0)
        ->and($request->body()->get('events'))->not()->toBeEmpty()
        ->and($request->body()->get('events')[0]['type'])->toBe('$page_view')
        ->and($request->body()->get('events')[0]['email'])->toBe('test@example.com')
        ->and($request->body()->get('events')[0]['fields'])->toBeNull()
        ->and($request->body()->get('events')[0]['details'])->toBeNull();
});

it('fails to create an event', function (): void {
    $mockClient = new MockClient([
        CreateEvents::class => MockResponse::make(body: [
            'results' => 0,
            'failed' => 1,
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = collect([
        new EventData(
            type: '$completed_onboarding',
            email: 'test@example.com',
            fields: [
                'first_name' => 'John',
                'last_name' => 'Doe',
            ],
        ),
    ]);

    $request = new CreateEvents($data);

    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('results'))->toBe(0)
        ->and($response->json('failed'))->toBe(1)
        ->and($request->body()->get('events'))->not()->toBeEmpty()
        ->and($request->body()->get('events')[0]['type'])->toBe('$completed_onboarding')
        ->and($request->body()->get('events')[0]['email'])->toBe('test@example.com')
        ->and($request->body()->get('events')[0]['fields'])->toBe([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
});

it('has an error when create an event', function (): void {
    $mockClient = new MockClient([
        CreateEvents::class => MockResponse::make(body: [], status: 500),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = collect([
        new EventData(
            type: '$completed_onboarding',
            email: 'test@example.com',
            fields: [
                'first_name' => 'John',
                'last_name' => 'Doe',
            ],
        ),
    ]);

    $request = new CreateEvents($data);

    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(500)
        ->and($response->json('results'))->toBeEmpty()
        ->and($request->body()->get('events'))->not()->toBeEmpty()
        ->and($request->body()->get('events')[0]['type'])->toBe('$completed_onboarding')
        ->and($request->body()->get('events')[0]['email'])->toBe('test@example.com')
        ->and($request->body()->get('events')[0]['fields'])->toBe([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
})->throws(InternalServerErrorException::class);

it('converts empty arrays to null in fields and details', function (): void {
    $mockClient = new MockClient([
        CreateEvents::class => MockResponse::make(body: [
            'results' => 1,
            'failed' => 0,
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = collect([
        new EventData(
            type: '$page_view',
            email: 'test@example.com',
            fields: [],
            details: [],
        ),
    ]);

    $request = new CreateEvents($data);

    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('results'))->toBe(1)
        ->and($response->json('failed'))->toBe(0)
        ->and($request->body()->get('events'))->not()->toBeEmpty()
        ->and($request->body()->get('events')[0]['type'])->toBe('$page_view')
        ->and($request->body()->get('events')[0]['email'])->toBe('test@example.com')
        ->and($request->body()->get('events')[0]['fields'])->toBeNull()
        ->and($request->body()->get('events')[0]['details'])->toBeNull();
});
