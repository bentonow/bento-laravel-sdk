<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Requests;

use Bentonow\BentoLaravel\DataTransferObjects\ReportStatsData;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetReportStats extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly ReportStatsData $data
    ) {}

    public function resolveEndpoint(): string
    {
        return '/stats/report';
    }

    protected function defaultQuery(): array
    {
        return [
            'report_id' => $this->data->report_id,
        ];
    }
}
