<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\DataTransferObjects;

use Bentonow\BentoLaravel\Enums\Command;

class CommandData
{
    public function __construct(
        public readonly Command $command,
        public readonly string $email,
        public readonly mixed $query,
    ) {}
}
