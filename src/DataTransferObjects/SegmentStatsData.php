<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\DataTransferObjects;

class SegmentStatsData
{
    public function __construct(
        public readonly string $segment_id
    ) {
    }
}
