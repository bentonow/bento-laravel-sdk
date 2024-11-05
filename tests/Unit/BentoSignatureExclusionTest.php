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

test('excluded parameters dont invalidate signature', function (): void {
    // Get the signed URL first
    $signedUrl = URL::signedRoute('test.route');

    // Get the signature parameters
    parse_str(parse_url($signedUrl, PHP_URL_QUERY), $params);

    // Create parameters array with excluded parameters
    $urlParams = [
        'utm_source' => 'test',
        'fbclid' => '123',
        'bento_uuid' => '456',
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

test('non excluded parameters invalidate signature', function (): void {
    // Get the signed URL first
    $signedUrl = URL::signedRoute('test.route');

    // Get the signature parameters
    parse_str(parse_url($signedUrl, PHP_URL_QUERY), $params);

    // Create parameters array with non-excluded parameter
    $urlParams = [
        'other_param' => 'test',
        'signature' => $params['signature'],
    ];

    // Add expires parameter if it exists in the original URL
    if (isset($params['expires'])) {
        $urlParams['expires'] = $params['expires'];
    }

    // Create URL with parameters
    $urlWithParams = '/test-route?'.http_build_query($urlParams);

    $this->get($urlWithParams)
        ->assertForbidden();
});

test('multiple excluded parameters are handled correctly', function (): void {
    // Get the signed URL first
    $signedUrl = URL::signedRoute('test.route');

    // Get the signature parameters
    parse_str(parse_url($signedUrl, PHP_URL_QUERY), $params);

    // Create parameters array with multiple excluded parameters
    $urlParams = [
        'utm_source' => 'facebook',
        'utm_medium' => 'social',
        'utm_campaign' => 'summer2024',
        'utm_content' => 'ad1',
        'utm_term' => 'sale',
        'fbclid' => 'abc123',
        'bento_uuid' => '456',
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

test('query parameters are preserved after validation', function (): void {
    // Get the signed URL first
    $signedUrl = URL::signedRoute('test.route');

    // Get the signature parameters
    parse_str(parse_url($signedUrl, PHP_URL_QUERY), $params);

    // Parameters to test
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

    // Verify parameters are preserved
    foreach ($testParams as $key => $value) {
        expect(request()->query($key))->toBe($value);
    }
});

// Optional: Test with temporary URL specifically
test('works with temporary signed urls', function (): void {
    $signedUrl = URL::temporarySignedRoute('test.route', now()->addMinutes(30));

    // Get the signature parameters
    parse_str(parse_url($signedUrl, PHP_URL_QUERY), $params);

    // Create parameters array
    $urlParams = [
        'utm_source' => 'test',
        'fbclid' => '123',
        'signature' => $params['signature'],
        'expires' => $params['expires'],
    ];

    // Create URL with parameters
    $urlWithParams = '/test-route?'.http_build_query($urlParams);

    $this->get($urlWithParams)
        ->assertOk();
});
