<?php

use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\DataTransferObjects\ContentModerationData;
use Bentonow\BentoLaravel\Requests\GetContentModeration;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('can check content moderation', function () {
    $mockClient = new MockClient([
        GetContentModeration::class => MockResponse::make(body: [
            'data' => [
                'valid' => true,
                'reasons' => [],
                'safe_original_content' => 'Its just so fluffy!',
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = new ContentModerationData('Its just so fluffy!');

    $request = new GetContentModeration($data);

    $response = $connector->send($request);
    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data')['valid'])->toBe(true)
        ->and($response->json('data')['reasons'])->toBeArray()->toBeEmpty()
        ->and($response->json('data')['safe_original_content'])->toBe('Its just so fluffy!')
        ->and($request->query()->get('content'))->not()->toBeEmpty()->toBe('Its just so fluffy!');
});
