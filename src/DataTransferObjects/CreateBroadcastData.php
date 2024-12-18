<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\DataTransferObjects;

use Bentonow\BentoLaravel\Enums\BroadcastType;

class CreateBroadcastData
{
    public function __construct(
        public readonly string $name,
        public readonly string $subject,
        public readonly string $content,
        public readonly BroadcastType $type,
        public readonly ContactData $from,
        public readonly string $inclusive_tags,
        public readonly string $exclusive_tags,
        public readonly string $segment_id,
        public readonly int $batch_size_per_hour,
        public readonly string $send_at,
        public readonly int $approved = 0,
    ) {}

    public function __toArray(): array
    {
        return [
            'name' => $this->name,
            'subject' => $this->subject,
            'content' => $this->content,
            'type' => $this->type->value,
            'from' => array_filter(['email' => $this->from->emailAddress, 'name' => $this->from->name]),
            'inclusive_tags' => $this->inclusive_tags,
            'exclusive_tags' => $this->exclusive_tags,
            'segment_id' => $this->segment_id,
            'batch_size_per_hour' => $this->batch_size_per_hour,
            'send_at' => $this->send_at,
            'approved' => $this->approved
        ];
    }
}
