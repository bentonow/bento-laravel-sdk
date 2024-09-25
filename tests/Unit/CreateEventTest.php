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
            ]
        ),
    ]);

    $request = new CreateEvents($data);

    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('results'))->toBe(1)
        ->and($response->json('failed'))->toBe(0)
        ->and($request->body()->get('events'))->not()->toBeEmpty()
        ->and($request->body()->get('events')[0]->type)->toBe('$completed_onboarding')
        ->and($request->body()->get('events')[0]->email)->toBe('test@example.com')
        ->and($request->body()->get('events')[0]->fields)->toBe([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

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
            ]
        ),
    ]);
    $request = new CreateEvents($data);
    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('results'))->toBe(0)
        ->and($response->json('failed'))->toBe(1)
        ->and($request->body()->get('events')[0]->type)->toBe('$completed_onboarding')
        ->and($request->body()->get('events')[0]->email)->toBe('test@example.com')
        ->and($request->body()->get('events')[0]->fields)->toBe([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

});

it('throws an internal exception (500) creating an event', function (): void {

    $mockClient = new MockClient([
        CreateEvents::class => MockResponse::make(body: [
        ], status: 500),
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
            ]
        ),
    ]);
    $response = $connector->send(new CreateEvents($data));

    expect($response->body())->not->toBeJson()
        ->and($response->status())->toBe(500);

})->throws(InternalServerErrorException::class);
