<?php

use Bentonow\BentoLaravel\BentoConnector;
use Bentonow\BentoLaravel\DataTransferObjects\ReportStatsData;
use Bentonow\BentoLaravel\Requests\GetReportStats;
use Saloon\Http\Auth\BasicAuthenticator;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('can get site stats', function () {
    $mockClient = new MockClient([
        GetReportStats::class => MockResponse::make(body: [
            'report_data' => [
                'data' => [],
                'chart_style' => 'count',
                'report_type' => 'Reporting::Reports::VisitorCountReport',
                'report_name' => 'New Subscribers',
            ],
        ], status: 200),
    ]);

    $connector = new BentoConnector;
    $connector->authenticate(new BasicAuthenticator('publish_key', 'secret_key'));
    $connector->withMockClient($mockClient);

    $data = new ReportStatsData('456');

    $request = new GetReportStats($data);

    $response = $connector->send($request);
    expect($response->body())->toBeJson()
        ->and($response->status())->toBe(200)
        ->and($response->json('report_data')['data'])->toBeArray()
        ->and($response->json('report_data')['chart_style'])->toBeString()->toBe('count')
        ->and($response->json('report_data')['report_type'])->toBeString()->toBe('Reporting::Reports::VisitorCountReport')
        ->and($response->json('report_data')['report_name'])->toBeString()->toBe('New Subscribers');
});
