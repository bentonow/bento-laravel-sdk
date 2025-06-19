<?php

namespace Bentonow\BentoLaravel\Console;

use Illuminate\Console\Command;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

class TestCommand extends Command
{
    protected $description = 'Send a test email using Bento transport';

    protected $name = 'bento:test';

    public function handle(): int
    {
        // Check if Bento is configured as the default mailer
        if (config('mail.default') !== 'bento') {
            $this->error('Bento is not configured as your default mailer.');
            $this->line('Please configure Bento for transactional emails in your .env file first (bento:install).');

            return self::FAILURE;
        }

        // Get the from address
        $fromAddress = config('mail.from.address');
        if (empty($fromAddress)) {
            $this->error('No from address configured.');
            $this->line('Please set MAIL_FROM_ADDRESS in your .env file.');

            return self::FAILURE;
        }

        $this->info('Sending test email to '.$fromAddress.'...');

        try {
            Mail::html('<p>This is a test email from your Laravel application using Bento transport.</p>', function (Message $message) use ($fromAddress) {
                $message->to($fromAddress)
                    ->subject('Bento Test Email')
                    ->text('This is a test email from your Laravel application using Bento transport.');
            });

            $this->info('Test email sent successfully! ✨');
            $this->line('Please check your inbox at '.$fromAddress);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to send test email:');
            $this->line($e->getMessage());

            return self::FAILURE;
        }
    }
}
