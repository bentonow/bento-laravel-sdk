<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\DataTransferObjects;

class EventData
{
    public function __construct(
        public readonly string $type,
        public readonly string $email,
        public readonly ?array $fields = null,
        public readonly ?array $details = null,
    ) {}
}
