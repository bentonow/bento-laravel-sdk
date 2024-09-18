<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\DataTransferObjects;

class BlacklistStatusData
{
    public function __construct(
        public readonly ?string $domain,
        public readonly ?string $ipAddress
    ) {
    }
}
