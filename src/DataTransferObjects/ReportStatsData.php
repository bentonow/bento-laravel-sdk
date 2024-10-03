<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\DataTransferObjects;

class ReportStatsData
{
    public function __construct(
        public readonly string $report_id
    ) {}
}
