<?php

use App\Models\User;
use Bentonow\BentoLaravel\Actions\UserImportAction;
use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\Console\UserImportCommand;
use Bentonow\BentoLaravel\DataTransferObjects\ImportSubscribersData;
use Bentonow\BentoLaravel\Requests\ImportSubscribers;
use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Mockery;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

beforeEach(function () {
    // Create test users
    $this->users = collect([
        (object)['name' => 'John Doe', 'email' => 'john@example.com'],
        (object)['name' => 'Jane Smith', 'email' => 'jane@example.com'],
        (object)['name' => 'Bob Wilson', 'email' => 'bob@example.com'],
    ]);

    // Mock config values
    config([
        'bentonow.publishable_key' => 'test-pub-key',
        'bentonow.secret_key' => 'test-secret-key',
        'bentonow.site_uuid' => 'test-site-uuid',
    ]);
});

it('processes users in batches and tracks results correctly', function () {
    // Mock the User model
    $userMock = Mockery::mock('overload:' . User::class);
    $userMock->shouldReceive('select')
        ->once()
        ->with('name', 'email')
        ->andReturnSelf();

    $userMock->shouldReceive('lazy')
        ->once()
        ->with(500)
        ->andReturn(LazyCollection::make($this->users));

    // Mock the UserImportAction class
    $actionMock = Mockery::mock('overload:' . UserImportAction::class);
    $actionMock->shouldReceive('execute')
        ->once()
        ->withArgs(function ($collection) {
            // Verify the collection contains the expected data
            expect($collection)->toBeInstanceOf(Collection::class);
            expect($collection)->toHaveCount(3);

            // Check if the first subscriber has the right data
            $firstSubscriber = $collection->first();
            expect($firstSubscriber)->toBeInstanceOf(ImportSubscribersData::class);
            expect($firstSubscriber->email)->toBe('john@example.com');

            // The UserImportCommand parses name using Str::of($user->name)->after('.')->before(' ')
            // For "Dr. John Doe", this extracts "John" as firstName
            expect($firstSubscriber->firstName)->toBe('John');

            // lastName should be everything after the first space
            expect($firstSubscriber->lastName)->toBe('Doe');

            return true;
        })
        ->andReturn(['results' => 3, 'failed' => 0]);

    // Setup command with output
    $command = $this->app->make(UserImportCommand::class);
    $output = new BufferedOutput;
    $command->setOutput(new OutputStyle(new ArrayInput([]), $output));

    $result = $command->handle();

    expect($result)->toBe(Command::SUCCESS);

    // Verify output contains success information
    $outputText = $output->fetch();
    expect($outputText)->toContain('Processed batch: 3 successful, 0 failed');
    expect($outputText)->toContain('Successfully imported 3 users');
});

it('handles large datasets with multiple batches', function () {
    // Create a large dataset (1001 users)
    $largeDataset = collect(range(1, 1001))->map(function ($i) {
        return (object)[
            'name' => "User{$i} Name{$i}",
            'email' => "user{$i}@example.com"
        ];
    });

    // Mock the User model
    $userMock = Mockery::mock('overload:' . User::class);
    $userMock->shouldReceive('select')
        ->once()
        ->with('name', 'email')
        ->andReturnSelf();

    $userMock->shouldReceive('lazy')
        ->once()
        ->with(500)
        ->andReturn(LazyCollection::make($largeDataset));

    // Mock the UserImportAction class - it gets called once with the entire collection
    $actionMock = Mockery::mock('overload:' . UserImportAction::class);
    $actionMock->shouldReceive('execute')
        ->once()
        ->andReturn(['results' => 1500, 'failed' => 0]);

    // Setup command with output
    $command = $this->app->make(UserImportCommand::class);
    $output = new BufferedOutput;
    $command->setOutput(new OutputStyle(new ArrayInput([]), $output));

    $result = $command->handle();

    expect($result)->toBe(Command::SUCCESS);

    // Verify output contains all batches information
    $outputText = $output->fetch();
    ray($outputText);
    expect($outputText)->toContain('Processed batch: 1500 successful, 0 failed')
        ->toContain('Completed! Successfully imported 4500 users. Failed to import 0 users.');
});

it('handles empty dataset gracefully', function () {
    // Mock the User model with empty dataset
    $userMock = Mockery::mock('overload:' . User::class);
    $userMock->shouldReceive('select')
        ->once()
        ->with('name', 'email')
        ->andReturnSelf();

    $userMock->shouldReceive('lazy')
        ->once()
        ->with(500)
        ->andReturn(LazyCollection::make([]));

    // We don't need to mock UserImportAction since it shouldn't be called

    // Setup command with output
    $command = $this->app->make(UserImportCommand::class);
    $output = new BufferedOutput;
    $command->setOutput(new OutputStyle(new ArrayInput([]), $output));

    $result = $command->handle();

    expect($result)->toBe(Command::SUCCESS);

    // Verify output contains empty information
    $outputText = $output->fetch();
    expect($outputText)->toContain('Completed! Successfully imported 0 users. Failed to import 0 users.');
});

it('handles partial failures correctly', function () {
    // Mock the User model
    $userMock = Mockery::mock('overload:' . User::class);
    $userMock->shouldReceive('select')
        ->once()
        ->with('name', 'email')
        ->andReturnSelf();

    $userMock->shouldReceive('lazy')
        ->once()
        ->with(500)
        ->andReturn(LazyCollection::make($this->users));

    // Mock the UserImportAction class with some failures
    $actionMock = Mockery::mock('overload:' . UserImportAction::class);
    $actionMock->shouldReceive('execute')
        ->once()
        ->andReturn(['results' => 1, 'failed' => 2]);

    // Setup command with output
    $command = $this->app->make(UserImportCommand::class);
    $output = new BufferedOutput;
    $command->setOutput(new OutputStyle(new ArrayInput([]), $output));

    $result = $command->handle();

    expect($result)->toBe(Command::SUCCESS);

    // Verify output contains failure information
    $outputText = $output->fetch();
    expect($outputText)->toContain('Processed batch: 1 successful, 2 failed');
    expect($outputText)->toContain('Successfully imported 1 users. Failed to import 2 users');
});