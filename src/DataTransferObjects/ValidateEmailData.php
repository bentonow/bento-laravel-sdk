<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\DataTransferObjects;

class ValidateEmailData
{
    public function __construct(
        public readonly string $emailAddress,
        public readonly ?string $fullName,
        public readonly ?string $userAgent,
        public ?string $ipAddress,
    ) {
        $this->ipAddress = $this->validateIpAddress($ipAddress);
    }

    protected function validateIpAddress(?string $ipAddress): ?string
    {
        if (empty($ipAddress)) {
            return null;
        }

        return (new GeoLocateIpData($ipAddress))->ipAddress;
    }
}
