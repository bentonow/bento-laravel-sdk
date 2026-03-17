<?php

use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\DataTransferObjects\UpdateEmailTemplateData;
use Bentonow\BentoLaravel\Requests\UpdateEmailTemplate;
use Saloon\Exceptions\Request\Statuses\InternalServerErrorException;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('can update an email template', function () {
    $mockClient = new MockClient([
        UpdateEmailTemplate::class => MockResponse::make(body: [
            'data' => [
                'id' => '123',
                'type' => 'email_templates',
                'attributes' => [
                    'subject' => 'Updated Subject',
                    'html' => '<p>Updated</p>',
                ],
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = new UpdateEmailTemplateData(id: 123, subject: 'Updated Subject', html: '<p>Updated</p>');
    $request = new UpdateEmailTemplate($data);
    $response = $connector->send($request);

    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($request->body()->get('email_template'))->toBe([
            'subject' => 'Updated Subject',
            'html' => '<p>Updated</p>',
        ]);
});

it('can update only the subject', function () {
    $mockClient = new MockClient([
        UpdateEmailTemplate::class => MockResponse::make(body: [
            'data' => [
                'id' => '123',
                'type' => 'email_templates',
                'attributes' => [
                    'subject' => 'New Subject',
                ],
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = new UpdateEmailTemplateData(id: 123, subject: 'New Subject');
    $request = new UpdateEmailTemplate($data);
    $response = $connector->send($request);

    expect($response->status())->toBe(200)
        ->and($request->body()->get('email_template'))->toBe([
            'subject' => 'New Subject',
        ]);
});

it('can update only the html', function () {
    $mockClient = new MockClient([
        UpdateEmailTemplate::class => MockResponse::make(body: [
            'data' => [
                'id' => '123',
                'type' => 'email_templates',
                'attributes' => [
                    'html' => '<p>New HTML</p>',
                ],
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = new UpdateEmailTemplateData(id: 123, html: '<p>New HTML</p>');
    $request = new UpdateEmailTemplate($data);
    $response = $connector->send($request);

    expect($response->status())->toBe(200)
        ->and($request->body()->get('email_template'))->toBe([
            'html' => '<p>New HTML</p>',
        ]);
});

it('throws on 500 for update email template', function () {
    $mockClient = new MockClient([
        UpdateEmailTemplate::class => MockResponse::make(body: [], status: 500),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = new UpdateEmailTemplateData(id: 123, subject: 'Test');
    $request = new UpdateEmailTemplate($data);
    $connector->send($request);
})->throws(InternalServerErrorException::class);
