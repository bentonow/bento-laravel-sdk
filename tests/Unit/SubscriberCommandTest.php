<?php

use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\DataTransferObjects\CommandData;
use Bentonow\BentoLaravel\Enums\Command;
use Bentonow\BentoLaravel\Requests\SubscriberCommand;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('can add a Tag to subscriber', function () {
    $mockClient = new MockClient([
        SubscriberCommand::class => MockResponse::make(body: [
            'data' => [
                'results' => 1,
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = collect([
        new CommandData(
            Command::ADD_TAG,
            'test@example.com',
            'test'
        ),
    ]);

    $request = new SubscriberCommand($data);

    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data')['results'])->toBeInt()->not()->toBeEmpty()->toBe(1)
        ->and($request->body()->get('command'))->toBeArray()->not()->toBeEmpty()
        ->and($request->body()->get('command')[0]->command->value)->toBeString()->toBe('add_tag')
        ->and($request->body()->get('command')[0]->email)->toBeString()->toBe('test@example.com')
        ->and($request->body()->get('command')[0]->query)->toBeString()->toBe('test');
});

it('can remove a Tag from subscriber', function () {
    $mockClient = new MockClient([
        SubscriberCommand::class => MockResponse::make(body: [
            'data' => [
                'results' => 1,
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = collect([
        new CommandData(
            Command::REMOVE_TAG,
            'test@example.com',
            'test'
        ),
    ]);

    $request = new SubscriberCommand($data);

    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data')['results'])->toBeInt()->not()->toBeEmpty()->toBe(1)
        ->and($request->body()->get('command'))->toBeArray()->not()->toBeEmpty()
        ->and($request->body()->get('command')[0]->command->value)->toBeString()->toBe('remove_tag')
        ->and($request->body()->get('command')[0]->email)->toBeString()->toBe('test@example.com')
        ->and($request->body()->get('command')[0]->query)->toBeString()->toBe('test');
});

it('can add a Tag by event to subscriber', function () {
    $mockClient = new MockClient([
        SubscriberCommand::class => MockResponse::make(body: [
            'data' => [
                'results' => 1,
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = collect([
        new CommandData(
            Command::ADD_TAG_VIA_EVENT,
            'test@example.com',
            'test'
        ),
    ]);

    $request = new SubscriberCommand($data);

    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data')['results'])->toBeInt()->not()->toBeEmpty()->toBe(1)
        ->and($request->body()->get('command'))->toBeArray()->not()->toBeEmpty()
        ->and($request->body()->get('command')[0]->command->value)->toBeString()->toBe('add_tag_via_event')
        ->and($request->body()->get('command')[0]->email)->toBeString()->toBe('test@example.com')
        ->and($request->body()->get('command')[0]->query)->toBeString()->toBe('test');
});

it('can add a field to subscriber', function () {
    $mockClient = new MockClient([
        SubscriberCommand::class => MockResponse::make(body: [
            'data' => [
                'results' => 1,
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = collect([
        new CommandData(
            Command::ADD_FIELD,
            'test@example.com',
            'test'
        ),
    ]);

    $request = new SubscriberCommand($data);

    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data')['results'])->toBeInt()->not()->toBeEmpty()->toBe(1)
        ->and($request->body()->get('command'))->toBeArray()->not()->toBeEmpty()
        ->and($request->body()->get('command')[0]->command->value)->toBeString()->toBe('add_field')
        ->and($request->body()->get('command')[0]->email)->toBeString()->toBe('test@example.com')
        ->and($request->body()->get('command')[0]->query)->toBeString()->toBe('test');
});

it('can remove a field to subscriber', function () {
    $mockClient = new MockClient([
        SubscriberCommand::class => MockResponse::make(body: [
            'data' => [
                'results' => 1,
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = collect([
        new CommandData(
            Command::REMOVE_FIELD,
            'test@example.com',
            'test'
        ),
    ]);

    $request = new SubscriberCommand($data);

    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data')['results'])->toBeInt()->not()->toBeEmpty()->toBe(1)
        ->and($request->body()->get('command'))->toBeArray()->not()->toBeEmpty()
        ->and($request->body()->get('command')[0]->command->value)->toBeString()->toBe('remove_field')
        ->and($request->body()->get('command')[0]->email)->toBeString()->toBe('test@example.com')
        ->and($request->body()->get('command')[0]->query)->toBeString()->toBe('test');
});

it('can subscribe a subscriber', function () {
    $mockClient = new MockClient([
        SubscriberCommand::class => MockResponse::make(body: [
            'data' => [
                'results' => 1,
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = collect([
        new CommandData(
            Command::SUBSCRIBE,
            'test@example.com',
            'test'
        ),
    ]);

    $request = new SubscriberCommand($data);

    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data')['results'])->toBeInt()->not()->toBeEmpty()->toBe(1)
        ->and($request->body()->get('command'))->toBeArray()->not()->toBeEmpty()
        ->and($request->body()->get('command')[0]->command->value)->toBeString()->toBe('subscribe')
        ->and($request->body()->get('command')[0]->email)->toBeString()->toBe('test@example.com')
        ->and($request->body()->get('command')[0]->query)->toBeString()->toBe('test');
});

it('can unsubscribe a subscriber', function () {
    $mockClient = new MockClient([
        SubscriberCommand::class => MockResponse::make(body: [
            'data' => [
                'results' => 1,
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = collect([
        new CommandData(
            Command::UNSUBSCRIBE,
            'test@example.com',
            'test'
        ),
    ]);

    $request = new SubscriberCommand($data);

    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data')['results'])->toBeInt()->not()->toBeEmpty()->toBe(1)
        ->and($request->body()->get('command'))->toBeArray()->not()->toBeEmpty()
        ->and($request->body()->get('command')[0]->command->value)->toBeString()->toBe('unsubscribe')
        ->and($request->body()->get('command')[0]->email)->toBeString()->toBe('test@example.com')
        ->and($request->body()->get('command')[0]->query)->toBeString()->toBe('test');
});

it('can change a subscriber email', function () {
    $mockClient = new MockClient([
        SubscriberCommand::class => MockResponse::make(body: [
            'data' => [
                'results' => 1,
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = collect([
        new CommandData(
            Command::CHANGE_EMAIL,
            'test@example.com',
            'test2@example.com'
        ),
    ]);

    $request = new SubscriberCommand($data);

    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data')['results'])->toBeInt()->not()->toBeEmpty()->toBe(1)
        ->and($request->body()->get('command'))->toBeArray()->not()->toBeEmpty()
        ->and($request->body()->get('command')[0]->command->value)->toBeString()->toBe('change_email')
        ->and($request->body()->get('command')[0]->email)->toBeString()->toBe('test@example.com')
        ->and($request->body()->get('command')[0]->query)->toBeString()->toBe('test2@example.com');
});

it('removes array keys from commands collection when serializing', function () {
    $data = collect([
        5 => new CommandData(
            Command::ADD_TAG,
            'user1@example.com',
            'tag1'
        ),
        10 => new CommandData(
            Command::REMOVE_TAG,
            'user2@example.com',
            'tag2'
        ),
        15 => new CommandData(
            Command::ADD_FIELD,
            'user3@example.com',
            'field1'
        ),
    ]);

    $request = new SubscriberCommand($data);
    $body = $request->body()->all();
    

    expect($body['command'])->toBeArray()
        ->and($body['command'])->toHaveCount(3)
        ->and(array_keys($body['command']))->toBe([0, 1, 2])
        ->and($body['command'][0]->command->value)->toBe('add_tag')
        ->and($body['command'][1]->command->value)->toBe('remove_tag')
        ->and($body['command'][2]->command->value)->toBe('add_field');
});
