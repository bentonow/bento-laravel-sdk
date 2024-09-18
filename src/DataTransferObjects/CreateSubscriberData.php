<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\DataTransferObjects;

class CreateSubscriberData
{
    public function __construct(
        public readonly string $email
    ) {
    }
}
