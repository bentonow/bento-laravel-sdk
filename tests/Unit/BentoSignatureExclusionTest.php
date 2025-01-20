<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

beforeEach(function (): void {
    Route::get('/test-route', fn () => response()->json(['status' => 'ok']))
        ->name('test.route')
        ->middleware('bento.signature');
});

test('valid signature passes', function (): void {
    $signedUrl = URL::signedRoute('test.route');

    $this->get($signedUrl)
        ->assertOk();
});

test('invalid signature fails', function (): void {
    $this->get('test-route?signature=invalid')
        ->assertForbidden();
});

test('additional parameters dont invalidate signature', function (): void {
    // Get the signed URL first
    $signedUrl = URL::signedRoute('test.route');

    // Get the signature parameters
    parse_str(parse_url($signedUrl, PHP_URL_QUERY), $params);

    // Create parameters array with additional parameters
    $urlParams = [
        'random_param' => 'test',
        'tracking_id' => '123',
        'user_id' => '456',
        'signature' => $params['signature'],
    ];

    // Add expires parameter if it exists in the original URL
    if (isset($params['expires'])) {
        $urlParams['expires'] = $params['expires'];
    }

    // Create URL with parameters
    $urlWithParams = '/test-route?'.http_build_query($urlParams);

    $this->get($urlWithParams)
        ->assertOk();
});

test('missing required parameters invalidate signature', function (): void {
    // Get the signed URL first
    $signedUrl = URL::temporarySignedRoute('test.route', now()->addMinutes(30));

    // Get the signature parameters
    parse_str(parse_url($signedUrl, PHP_URL_QUERY), $params);

    // Create URL with only signature (missing expires)
    $urlWithParams = '/test-route?signature=' . $params['signature'];

    $this->get($urlWithParams)
        ->assertForbidden();
});

test('multiple additional parameters are handled correctly', function (): void {
    // Get the signed URL first
    $signedUrl = URL::signedRoute('test.route');

    // Get the signature parameters
    parse_str(parse_url($signedUrl, PHP_URL_QUERY), $params);

    // Create parameters array with multiple additional parameters
    $urlParams = [
        'utm_source' => 'facebook',
        'utm_medium' => 'social',
        'utm_campaign' => 'summer2024',
        'utm_content' => 'ad1',
        'utm_term' => 'sale',
        'fbclid' => 'abc123',
        'bento_uuid' => '456',
        'random_param1' => 'value1',
        'random_param2' => 'value2',
        'signature' => $params['signature'],
    ];

    // Add expires parameter if it exists in the original URL
    if (isset($params['expires'])) {
        $urlParams['expires'] = $params['expires'];
    }

    // Create URL with parameters
    $urlWithParams = '/test-route?'.http_build_query($urlParams);

    $this->get($urlWithParams)
        ->assertOk();
});

test('query parameters are cleaned after validation', function (): void {
    // Get the signed URL first
    $signedUrl = URL::signedRoute('test.route');

    // Get the signature parameters
    parse_str(parse_url($signedUrl, PHP_URL_QUERY), $params);

    // Parameters that should be removed
    $testParams = [
        'utm_source' => 'facebook',
        'fbclid' => 'abc123',
    ];

    // Create parameters array with test parameters
    $urlParams = array_merge($testParams, [
        'signature' => $params['signature'],
    ]);

    // Add expires parameter if it exists in the original URL
    if (isset($params['expires'])) {
        $urlParams['expires'] = $params['expires'];
    }

    // Create URL with parameters
    $urlWithParams = '/test-route?'.http_build_query($urlParams);

    $response = $this->get($urlWithParams);
    $response->assertOk();

    // Verify additional parameters are removed
    foreach ($testParams as $key => $value) {
        expect(request()->query($key))->toBeNull();
    }

    // Verify required parameters are preserved
    expect(request()->query('signature'))->toBe($params['signature']);
    if (isset($params['expires'])) {
        expect(request()->query('expires'))->toBe($params['expires']);
    }
});

test('works with temporary signed urls', function (): void {
    $signedUrl = URL::temporarySignedRoute('test.route', now()->addMinutes(30));

    // Get the signature parameters
    parse_str(parse_url($signedUrl, PHP_URL_QUERY), $params);

    // Create parameters array with additional parameters
    $urlParams = [
        'utm_source' => 'test',
        'fbclid' => '123',
        'random_param' => 'value',
        'signature' => $params['signature'],
        'expires' => $params['expires'],
    ];

    // Create URL with parameters
    $urlWithParams = '/test-route?'.http_build_query($urlParams);

    $this->get($urlWithParams)
        ->assertOk();
});
