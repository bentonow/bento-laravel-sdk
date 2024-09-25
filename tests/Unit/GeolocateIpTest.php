<?php

declare(strict_types=1);

use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\DataTransferObjects\GeoLocateIpData;
use Bentonow\BentoLaravel\Requests\GeoLocateIP;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('can geolocate an ip address', function (): void {
    $mockClient = new MockClient([
        GeoLocateIP::class => MockResponse::make(body: [
            'data' => [
                [
                    'request' => '1.1.1.1',
                    'ip' => '1.1.1.1',
                    'country_code2' => 'JP',
                    'country_code3' => 'JPN',
                    'country_name' => 'Japan',
                    'continent_code' => 'AS',
                    'region_name' => '42',
                    'city_name' => 'Tokyo',
                    'postal_code' => '206-0000',
                    'latitude' => 35.6895,
                    'longitude' => 139.69171,
                    'dma_code' => null,
                    'area_code' => null,
                    'timezone' => 'Asia/Tokyo',
                    'real_region_name' => 'Tokyo',
                ],

            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = new GeoLocateIpData('1.1.1.1');

    $request = new GeoLocateIP($data);

    $response = $connector->send($request);
    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('data')[0]['ip'])->toBe('1.1.1.1')
        ->and($response->json('data')[0]['country_code2'])->toBe('JP')
        ->and($response->json('data')[0]['country_code3'])->toBe('JPN')
        ->and($response->json('data')[0]['country_name'])->toBe('Japan')
        ->and($response->json('data')[0]['continent_code'])->toBe('AS')
        ->and($response->json('data')[0]['region_name'])->toBe('42')
        ->and($response->json('data')[0]['city_name'])->toBe('Tokyo')
        ->and($response->json('data')[0]['postal_code'])->toBe('206-0000')
        ->and($response->json('data')[0]['latitude'])->toBe(35.6895)
        ->and($response->json('data')[0]['longitude'])->toBe(139.69171)
        ->and($response->json('data')[0]['dma_code'])->toBeNull()
        ->and($response->json('data')[0]['area_code'])->toBeNull()
        ->and($response->json('data')[0]['timezone'])->toBe('Asia/Tokyo')
        ->and($response->json('data')[0]['real_region_name'])->toBe('Tokyo')
        ->and($request->query()->get('ip'))->not()->toBeEmpty()->toBe('1.1.1.1');
});
