<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\DataTransferObjects;

class CreateTagData
{
    public function __construct(
        public readonly string $name,
    ) {}
}
