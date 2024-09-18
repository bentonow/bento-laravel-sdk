<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\DataTransferObjects;

class ContactData
{
    public function __construct(
        public readonly string $emailAddress,
        public readonly ?string $name,
    ) {
    }
}
