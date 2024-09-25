<?php

namespace Bentonow\BentoLaravel\DataTransferObjects;

class GenderData
{
    public function __construct(
        public readonly string $fullName,
    ) {}
}
