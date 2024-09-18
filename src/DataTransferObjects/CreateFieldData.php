<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\DataTransferObjects;

class CreateFieldData
{
    public function __construct(
        public readonly string $key,
    ) {
    }
}
