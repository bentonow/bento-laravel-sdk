<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\DataTransferObjects;

class CreateSequenceEmailData
{
    public function __construct(
        public readonly string $sequenceId,
        public readonly string $subject,
        public readonly string $html,
        public readonly ?string $inboxSnippet = null,
        public readonly ?string $delayInterval = null,
        public readonly ?int $delayIntervalCount = null,
        public readonly ?string $editorChoice = null,
        public readonly ?string $cc = null,
        public readonly ?string $bcc = null,
        public readonly ?string $to = null,
    ) {}
}
