<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\DataTransferObjects;

class UpdateEmailTemplateData
{
    public function __construct(
        public readonly int $id,
        public readonly ?string $subject = null,
        public readonly ?string $html = null,
    ) {}
}
