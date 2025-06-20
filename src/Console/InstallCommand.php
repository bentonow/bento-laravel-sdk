<?php

namespace Bentonow\BentoLaravel\Console;

use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;

class InstallCommand extends Command
{
    protected $description = 'Install the Bentonow configuration file.';

    protected $name = 'bento:install';

    private function displayHeader(): void
    {
        $this->line('');
        $this->line('     ██████╗ ███████╗███╗   ██╗████████╗ ██████╗ ');
        $this->line('     ██╔══██╗██╔════╝████╗  ██║╚══██╔══╝██╔═══██╗');
        $this->line('     ██████╔╝█████╗  ██╔██╗ ██║   ██║   ██║   ██║');
        $this->line('     ██╔══██╗██╔══╝  ██║╚██╗██║   ██║   ██║   ██║');
        $this->line('     ██████╔╝███████╗██║ ╚████║   ██║   ╚██████╔╝');
        $this->line('     ╚═════╝ ╚══════╝╚═╝  ╚═══╝   ╚═╝    ╚═════╝');

        $this->line('');
        $this->line('                Welcome to Bento!');
    }

    public function handle(): int
    {
        $this->displayHeader();

        $this->callSilently('vendor:publish', [
            '--tag' => 'bento-config',
        ]);

        $publishableKey = text(
            label: 'Enter your Bento Publishable Key',
            placeholder: 'BENTO_PUBLISHABLE_KEY',
            required: true
        );

        $secretKey = text(
            label: 'Enter your Bento Secret Key',
            placeholder: 'BENTO_SECRET_KEY',
            required: true
        );

        $siteUuid = text(
            label: 'Enter your Bento Site UUID',
            placeholder: 'BENTO_SITE_UUID',
            required: true
        );

        $enableTransactionalMail = confirm(
            label: 'Would you like to enable Bento for transactional emails?',
            default: true
        );

        $authorEmail = '';
        $sendTestEmail = false;
        if ($enableTransactionalMail) {
            $authorEmail = text(
                label: 'Enter the author email for transactional mail',
                placeholder: 'author@example.com',
                required: true
            );

            $sendTestEmail = confirm(
                label: 'Would you like to send a test email after configuration?',
                default: true
            );
        }

        $shouldModifyEnv = confirm(
            label: 'Would you like to automatically update your .env file with these values?',
            default: true
        );

        if ($shouldModifyEnv) {
            $envVars = [
                'BENTO_PUBLISHABLE_KEY' => $publishableKey,
                'BENTO_SECRET_KEY' => $secretKey,
                'BENTO_SITE_UUID' => $siteUuid,
            ];

            if ($enableTransactionalMail) {
                $envVars['MAIL_MAILER'] = 'bento';
                $envVars['MAIL_FROM_ADDRESS'] = $authorEmail;
            }

            $this->updateEnv($envVars);
            $this->info('Your .env file has been updated with your Bento credentials.');

            if ($sendTestEmail) {
                $this->line('');
                $this->info('Sending test email...');
                $this->call('bento:test');
            }
        } else {
            $this->warn("\nBento was not able to update your .env file.\n");
            $this->line('Please add the following to your .env file:');
            $this->line('');
            $this->line("BENTO_PUBLISHABLE_KEY=\"$publishableKey\"");
            $this->line("BENTO_SECRET_KEY=\"$secretKey\"");
            $this->line("BENTO_SITE_UUID=\"$siteUuid\"");
            if ($enableTransactionalMail) {
                $this->line('MAIL_MAILER=bento');
                $this->line("MAIL_FROM_ADDRESS=\"$authorEmail\"");
            }
            $this->line('');
            $this->line('Learn more: https://docs.bentonow.com/laravel or https://app.bentonow.com');
        }

        if (confirm('Would you like to star the repo on GitHub?')) {
            $url = 'https://github.com/bentonow/bento-laravel-sdk';

            $command = [
                'Darwin' => 'open',
                'Linux' => 'xdg-open',
                'Windows' => 'start',
            ][PHP_OS_FAMILY] ?? null;

            if ($command) {
                exec("{$command} {$url}");
            }
        }

        $this->info('Bento has been installed!');

        return self::SUCCESS;
    }

    protected function updateEnv(array $data): void
    {
        $envPath = base_path('.env');
        if (! file_exists($envPath)) {
            $this->warn('.env file not found.');

            return;
        }
        $env = file_get_contents($envPath);
        foreach ($data as $key => $value) {
            $pattern = "/^{$key}=.*$/m";
            $line = "{$key}=\"{$value}\"";
            if (preg_match($pattern, $env)) {
                $env = preg_replace($pattern, $line, $env);
            } else {
                $env .= "\n{$line}";
            }
        }
        file_put_contents($envPath, $env);
    }
}
