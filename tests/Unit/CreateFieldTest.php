<?php

declare(strict_types=1);

use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\DataTransferObjects\CreateFieldData;
use Bentonow\BentoLaravel\Requests\CreateField;
use Saloon\Exceptions\Request\Statuses\InternalServerErrorException;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('can create a field', function (): void {
    $mockClient = new MockClient([
        CreateField::class => MockResponse::make(body: [
            'data' => [
                'id' => 'abc123',
                'type' => 'visitors-fields',
                'attributes' => [
                    'name' => 'First Name',
                    'key' => 'first_name',
                    'whitelisted' => null,
                ],
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = new CreateFieldData(
        key: 'First Name'
    );

    $request = new CreateField($data);

    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data')['id'])->toBe('abc123')
        ->and($response->json('data')['attributes']['key'])->toBe('first_name')
        ->and($response->json('data')['attributes']['name'])->toBe('First Name')
        ->and($request->body()->get('field'))->not()->toBeEmpty()
        ->and($request->body()->get('field')['key'])->toBe('First Name');
});

it('fails to create server error (500) a field', function (): void {
    $mockClient = new MockClient([
        CreateField::class => MockResponse::make(body: [

        ], status: 500),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = new CreateFieldData(
        key: 'First Name'
    );

    $request = new CreateField($data);

    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(500)
        ->and($response->json('data'))->toBeEmpty()
        ->and($request->body()->get('field'))->not()->toBeEmpty()
        ->and($request->body()->get('field')->key)->toBe('first_name');
})->throws(InternalServerErrorException::class);
