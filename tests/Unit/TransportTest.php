<?php

use Bentonow\BentoLaravel\BentoTransport;
use Bentonow\BentoLaravel\Tests\Unit\TestMailable;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    config()->set(['app.mailer.bento.transport' => 'bento']);
    Mail::fake();
});

test('confirm mailer is set to bento', function () {
    expect(config('app.mailer.bento.transport'))->toBe('bento');
});

test('validate recipient', function () {

    Mail::assertNothingSent();

    Mail::to('test@example.com')->send(new TestMailable);

    Mail::assertSent(TestMailable::class, 'test@example.com');

});

test('validate sender', function () {

    Mail::assertNothingSent();

    Mail::to('test@example.com')->send(new TestMailable);

    Mail::assertSent(TestMailable::class, function ($mail) {
        return $mail->hasFrom('test@example.com');
    });

});

it('can get transport', function () {

    $this->transporter = new BentoTransport;

    $app = app();

    $manager = $app->get(MailManager::class);

    $transport = $manager->createSymfonyTransport(['transport' => 'bento']);

    expect((string) $transport)->toBe('bento');
});

it('can send with bento transport', function () {

    $this->transporter = new BentoTransport;

    $app = app();

    $manager = $app->get(MailManager::class);

    $transport = $manager->createSymfonyTransport(['transport' => 'bento']);

    Mail::assertNothingSent();

    Mail::to('test@example.com')->send(new TestMailable);

    Mail::assertSent(TestMailable::class, 'test@example.com');

});
