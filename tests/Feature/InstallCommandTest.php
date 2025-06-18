<?php

declare(strict_types=1);

use function Pest\Laravel\artisan;

it('shows a notice and does not update .env if declined', function () {
    $envPath = base_path('.env.testing');
    file_put_contents($envPath, "");

    $process = artisan('bento:install')
        ->expectsQuestion('Enter your Bento Publishable Key', 'test-publishable-key')
        ->expectsQuestion('Enter your Bento Secret Key', 'test-secret-key')
        ->expectsQuestion('Enter your Bento Site UUID', 'test-site-uuid')
        ->expectsQuestion('Enter the author email for transactional mail', 'author@example.com')
        ->expectsConfirmation('Would you like to automatically update your .env file with these values?', 'no')
        ->expectsConfirmation('Would you like to star the repo on GitHub?', 'no')
        ->assertExitCode(0);

    $env = file_get_contents($envPath);
    expect($env)->not()->toContain('BENTO_PUBLISHABLE_KEY="test-publishable-key"');
    expect($env)->not()->toContain('BENTO_SECRET_KEY="test-secret-key"');
    expect($env)->not()->toContain('BENTO_SITE_UUID="test-site-uuid"');
    expect($env)->not()->toContain('MAIL_FROM_ADDRESS="author@example.com"');
});
