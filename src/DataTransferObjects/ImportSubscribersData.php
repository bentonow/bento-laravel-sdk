<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\DataTransferObjects;

class ImportSubscribersData
{
    public function __construct(
        public readonly string $email,
        public readonly ?string $firstName,
        public readonly ?string $lastName,
        public readonly ?array $tags,
        public readonly ?array $removeTags,
        public readonly ?array $fields,
    ) {
    }

    public function __toArray(): array
    {
        return array_filter(array_merge([
            'email' => $this->email,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'tags' => join(',', $this->tags),
            'remove_tags' => join(',', $this->removeTags)
        ], $this->fields));
    }
}
