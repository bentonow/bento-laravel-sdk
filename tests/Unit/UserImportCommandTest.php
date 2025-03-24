<?php

use App\Models\User;
use Bentonow\BentoLaravel\Console\UserImportCommand;
use Bentonow\BentoLaravel\Requests\ImportSubscribers;
use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\LazyCollection;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\PendingRequest;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

// Make all tests run in isolation (separate processes)
uses()->in('isolation');

beforeEach(function () {
    // Create test users
    $this->users = collect([
        (object) ['name' => 'John Doe', 'email' => 'john@example.com'],
        (object) ['name' => 'Jane Smith', 'email' => 'jane@example.com'],
        (object) ['name' => 'Bob Wilson', 'email' => 'bob@example.com'],
    ]);

    // Mock config values
    config([
        'bentonow.publishable_key' => 'test-pub-key',
        'bentonow.secret_key' => 'test-secret-key',
        'bentonow.site_uuid' => 'test-site-uuid',
    ]);

    $this->batchCallCount = 0;

    MockClient::global([
        ImportSubscribers::class => function (PendingRequest $request) {
            $subscribers = $request->body()?->get('subscribers') ?? [];
            $count = count($subscribers);
            $this->batchCallCount++;

            // Test: handles partial failures correctly (3 users)
            if ($count === 3 && $this->batchCallCount === 1) {
                return MockResponse::make(['results' => 1, 'failed' => 2]);
            }

            // Test: handles large datasets with multiple batches (1001 users split into 500, 500, 1)
            if ($count === 500 || $count === 1) {
                return MockResponse::make(['results' => $count, 'failed' => 0]);
            }

            // Default: full success
            return MockResponse::make(['results' => $count, 'failed' => 0]);
        },
    ]);
});

afterEach(function () {
    Mockery::close();
});

it('processes users in batches and tracks results correctly', function () {
    // Mock the User model
    $userMock = Mockery::mock('overload:'.User::class);
    $userMock->shouldReceive('select')
        ->once()
        ->with('name', 'email')
        ->andReturnSelf();

    $userMock->shouldReceive('lazy')
        ->once()
        ->with(500)
        ->andReturn(LazyCollection::make($this->users));

    // Create and run the command
    $command = $this->app->make(UserImportCommand::class);
    $output = new BufferedOutput;
    $command->setOutput(new OutputStyle(new ArrayInput([]), $output));

    $result = $command->handle();

    expect($result)->toBe(Command::SUCCESS);

    // Verify output
    $outputText = $output->fetch();
    expect($outputText)->toContain('Processed batch: 1 successful, 2 failed')
        ->toContain('Completed! Successfully imported 1 users. Failed to import 2 users.');
});

it('handles large datasets with multiple batches', function () {
    // Create a large dataset (1001 users)
    $largeDataset = collect(range(1, 1001))->map(function ($i) {
        return (object) [
            'name' => "User{$i} Name{$i}",
            'email' => "user{$i}@example.com",
        ];
    });

    // Mock the User model
    $userMock = Mockery::mock('overload:'.User::class);
    $userMock->shouldReceive('select')
        ->once()
        ->with('name', 'email')
        ->andReturnSelf();

    $userMock->shouldReceive('lazy')
        ->once()
        ->with(500)
        ->andReturn(LazyCollection::make($largeDataset));

    // Create and run the command
    $command = $this->app->make(UserImportCommand::class);
    $output = new BufferedOutput;
    $command->setOutput(new OutputStyle(new ArrayInput([]), $output));

    $result = $command->handle();

    expect($result)->toBe(Command::SUCCESS);

    // Verify output
    $outputText = $output->fetch();
    expect($outputText)->toContain('Processed batch: 500 successful, 0 failed')
        ->toContain('Completed! Successfully imported 1001 users. Failed to import 0 users.');
});

it('handles empty dataset gracefully', function () {
    // Mock the User model with empty dataset
    $userMock = Mockery::mock('overload:'.User::class);
    $userMock->shouldReceive('select')
        ->once()
        ->with('name', 'email')
        ->andReturnSelf();

    $userMock->shouldReceive('lazy')
        ->once()
        ->with(500)
        ->andReturn(LazyCollection::make([]));

    // Create and run the command
    $command = $this->app->make(UserImportCommand::class);
    $output = new BufferedOutput;
    $command->setOutput(new OutputStyle(new ArrayInput([]), $output));

    $result = $command->handle();

    expect($result)->toBe(Command::SUCCESS);

    // Verify output
    $outputText = $output->fetch();
    expect($outputText)->toContain('Completed! Successfully imported 0 users. Failed to import 0 users.');
});

it('handles partial failures correctly', function () {
    // Mock the User model
    $userMock = Mockery::mock('overload:'.User::class);
    $userMock->shouldReceive('select')
        ->once()
        ->with('name', 'email')
        ->andReturnSelf();

    $userMock->shouldReceive('lazy')
        ->once()
        ->with(500)
        ->andReturn(LazyCollection::make($this->users));

    // Create and run the command
    $command = $this->app->make(UserImportCommand::class);
    $output = new BufferedOutput;
    $command->setOutput(new OutputStyle(new ArrayInput([]), $output));

    $result = $command->handle();

    expect($result)->toBe(Command::SUCCESS);

    // Verify output
    $outputText = $output->fetch();
    expect($outputText)->toContain('Processed batch: 1 successful, 2 failed')
        ->toContain('Completed! Successfully imported 1 users. Failed to import 2 users.');
});
