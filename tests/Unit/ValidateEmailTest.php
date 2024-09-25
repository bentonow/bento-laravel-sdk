<?php

use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\DataTransferObjects\ValidateEmailData;
use Bentonow\BentoLaravel\Requests\ValidateEmail;
use Saloon\Exceptions\Request\Statuses\InternalServerErrorException;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('can validate an email', function () {
    $mockClient = new MockClient([
        ValidateEmail::class => MockResponse::make(body: [
            'data' => [
                'valid' => false,
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = new ValidateEmailData(
        emailAddress: 'test@example.com',
        fullName: 'John Snow',
        userAgent: null,
        ipAddress: null
    );

    $request = new ValidateEmail($data);

    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data')['valid'])->toBeBool()->toBe(false)
        ->and($request->query()->get('name'))->toBeString()->not()->toBeEmpty()->toBe('John Snow')
        ->and($request->query()->get('email'))->toBeString()->not()->toBeEmpty()->toBe('test@example.com')
        ->and($request->query()->get('ip'))->toBeEmpty();
});

it('fails to validate email (500)', function (): void {
    $mockClient = new MockClient([
        ValidateEmail::class => MockResponse::make(body: [], status: 500),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = new ValidateEmailData(
        emailAddress: 'test@example.com',
        fullName: 'John Snow',
        userAgent: null,
        ipAddress: null
    );

    $request = new ValidateEmail($data);

    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(500)
        ->and($response->json('data')['id'])->toBeEmpty();
})->throws(InternalServerErrorException::class);
