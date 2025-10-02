<?php

use Bentonow\BentoLaravel\Console\TestCommand;
use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

afterEach(function () {
    \Mockery::close();
});

function stripAnsiCodes(string $value): string
{
    return preg_replace('/\e\[[^m]*m/', '', $value);
}

it('fails when Bento is not configured as the default mailer', function () {
    config([
        'mail.default' => 'smtp',
        'mail.from.address' => 'sender@example.com',
    ]);

    $command = $this->app->make(TestCommand::class);
    $bufferedOutput = new BufferedOutput;
    $command->setOutput(new OutputStyle(new ArrayInput([]), $bufferedOutput));

    $result = $command->handle();

    expect($result)->toBe(Command::FAILURE);

    $output = stripAnsiCodes($bufferedOutput->fetch());

    expect($output)
        ->toContain('Bento is not configured as your default mailer.')
        ->toContain('Please configure Bento for transactional emails in your .env file first (bento:install).');
});

it('fails when no from address is configured', function () {
    config([
        'mail.default' => 'bento',
        'mail.from.address' => null,
    ]);

    $command = $this->app->make(TestCommand::class);
    $bufferedOutput = new BufferedOutput;
    $command->setOutput(new OutputStyle(new ArrayInput([]), $bufferedOutput));

    $result = $command->handle();

    expect($result)->toBe(Command::FAILURE);

    $output = stripAnsiCodes($bufferedOutput->fetch());

    expect($output)
        ->toContain('No from address configured.')
        ->toContain('Please set MAIL_FROM_ADDRESS in your .env file.');
});

it('sends the test email when configuration is valid', function () {
    config([
        'mail.default' => 'bento',
        'mail.from.address' => 'sender@example.com',
    ]);

    Mail::shouldReceive('html')
        ->once()
        ->with(
            '<p>This is a test email from your Laravel application using Bento transport.</p>',
            \Mockery::type('callable')
        )
        ->andReturnUsing(function (string $html, callable $callback) {
            $message = \Mockery::mock(Message::class);
            $message->shouldReceive('to')->once()->with('sender@example.com')->andReturnSelf();
            $message->shouldReceive('subject')->once()->with('Bento Test Email')->andReturnSelf();
            $message->shouldReceive('text')
                ->once()
                ->with('This is a test email from your Laravel application using Bento transport.')
                ->andReturnSelf();

            $callback($message);
        });

    $command = $this->app->make(TestCommand::class);
    $bufferedOutput = new BufferedOutput;
    $command->setOutput(new OutputStyle(new ArrayInput([]), $bufferedOutput));

    $result = $command->handle();

    expect($result)->toBe(Command::SUCCESS);

    $output = stripAnsiCodes($bufferedOutput->fetch());

    expect($output)
        ->toContain('Sending test email to sender@example.com...')
        ->toContain('Test email sent successfully! ✨')
        ->toContain('Please check your inbox at sender@example.com');
});
