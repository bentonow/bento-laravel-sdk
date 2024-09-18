<?php

namespace Bentonow\BentoLaravel\DataTransferObjects;

class ContentModerationData
{
    public function __construct(
        public readonly string $content,
    ) {
    }
}