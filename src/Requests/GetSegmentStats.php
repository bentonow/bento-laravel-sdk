<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Requests;

use Bentonow\BentoLaravel\DataTransferObjects\SegmentStatsData;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetSegmentStats extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly SegmentStatsData $data
    ) {}

    public function resolveEndpoint(): string
    {
        return '/stats/segment';
    }

    protected function defaultQuery(): array
    {
        return [
            'segment_id' => $this->data->segment_id,
        ];
    }
}
