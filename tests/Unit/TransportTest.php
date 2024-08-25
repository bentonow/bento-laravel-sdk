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

test('validate sender', function () {

    Mail::assertNothingSent();

    Mail::to('test@example.com')->send(new TestMailable);

    Mail::assertSent(TestMailable::class, function ($mail) {
        return $mail->hasFrom('test@example.com');
    });
});

test('validate single recipient', function () {

    Mail::assertNothingSent();

    Mail::to('test@example.com')->send(new TestMailable);

    Mail::assertSent(TestMailable::class, 'test@example.com');
});

test('validate multiple recipients', function () {

    Mail::assertNothingSent();

    Mail::to([
        ['email' => 'recipient1@example.com', 'name' => 'Recipient 1'],
        ['email' => 'recipient2@example.com', 'name' => 'Recipient 2'],
    ])
        ->send(new TestMailable);

    Mail::assertSent(TestMailable::class, 'recipient1@example.com');
    Mail::assertSent(TestMailable::class, 'recipient2@example.com');
});

test('validate single cc', function () {

    Mail::assertNothingSent();

    Mail::to('test@example.com')
        ->cc('recipient1@example.com')
        ->send(new TestMailable);

    Mail::assertSent(TestMailable::class, function ($mail) {
        return $mail->hasCc('recipient1@example.com');
    });
});

test('validate multiple ccs', function () {

    Mail::assertNothingSent();

    Mail::to('test@example.com')
        ->cc([
            ['email' => 'carboncopy1@example.com', 'name' => 'Carbon Copy'],
            ['email' => 'carboncopy2@example.com', 'name' => 'Carbon Copy'],
        ])
        ->send(new TestMailable);

    Mail::assertSent(TestMailable::class, function ($mail) {
        return $mail->hasCc(['carboncopy1@example.com', 'carboncopy2@example.com']);
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

    $manager->createSymfonyTransport(['transport' => 'bento']);

    Mail::assertNothingSent();

    Mail::to('test@example.com')->send(new TestMailable);

    Mail::assertSent(TestMailable::class, 'test@example.com');
});
