<?php

declare(strict_types=1);

use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\DataTransferObjects\CreateSubscriberData;
use Bentonow\BentoLaravel\Requests\CreateSubscriber;
use Saloon\Exceptions\Request\Statuses\InternalServerErrorException;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('can create a subscriber', function (): void {
    $mockClient = new MockClient([
        CreateSubscriber::class => MockResponse::make(body: [
            'data' => [
                'id' => '12345',
                'type' => 'visitors',
                'attributes' => [
                    'uuid' => '123-123-123-123',
                    'email' => 'test@example.com',
                    'fields' => [],
                    'cached_tag_ids' => [],
                ],
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = new CreateSubscriberData(
        email: 'test@example.com'
    );

    $request = new CreateSubscriber($data);

    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data')['id'])->toBe('12345')
        ->and($response->json('data')['attributes']['uuid'])->toBe('123-123-123-123')
        ->and($response->json('data')['attributes']['email'])->toBe('test@example.com')
        ->and($request->body()->get('subscriber'))->not()->toBeEmpty()
        ->and($request->body()->get('subscriber')['email'])->toBe('test@example.com');
});

it('fails to create a subscriber (500)', function (): void {
    $mockClient = new MockClient([
        CreateSubscriber::class => MockResponse::make(body: [
        ], status: 500),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = new CreateSubscriberData(
        email: 'test@example.com'
    );

    $request = new CreateSubscriber($data);

    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(500)
        ->and($response->json('data'))->toBeEmpty();
})->throws(InternalServerErrorException::class);
