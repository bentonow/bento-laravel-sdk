<?php

declare(strict_types=1);

use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\DataTransferObjects\CreateTagData;
use Bentonow\BentoLaravel\Requests\CreateTag;
use Saloon\Exceptions\Request\Statuses\InternalServerErrorException;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('can create a Tag', function (): void {
    $mockClient = new MockClient([
        CreateTag::class => MockResponse::make(body: [
            'data' => [
                'id' => 'abc123',
                'type' => 'tags',
                'attributes' => [
                    'name' => 'purchased',
                    'created_at' => '2024-08-06T05:44:04.444Z',
                    'discarded_at' => null,
                ],
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = new CreateTagData(
        name: 'purchased'
    );

    $request = new CreateTag($data);

    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data')['id'])->toBe('abc123')
        ->and($response->json('data')['attributes']['created_at'])->toBe('2024-08-06T05:44:04.444Z')
        ->and($response->json('data')['attributes']['name'])->toBe('purchased')
        ->and($request->body()->get('tag'))->not()->toBeEmpty()
        ->and($request->body()->get('tag')['name'])->toBe('purchased');
});

it('fails to create a Tag (500)', function (): void {
    $mockClient = new MockClient([
        CreateTag::class => MockResponse::make(body: [], status: 500),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = new CreateTagData(
        name: 'purchased'
    );

    $request = new CreateTag($data);

    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(500)
        ->and($response->json('data')['id'])->toBeEmpty();
})->throws(InternalServerErrorException::class);
