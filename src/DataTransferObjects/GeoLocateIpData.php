<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\DataTransferObjects;

use Exception;
use Throwable;

class GeoLocateIpData
{
    public function __construct(
        public string $ipAddress,
    ) {
        $this->ipAddress = $this->validateIpAddress($ipAddress);
    }

    protected function validateIpAddress($ipAddress): string
    {
        try {
            throw_unless(
                filter_var(
                    $ipAddress,
                    FILTER_VALIDATE_IP,
                    FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
                ),
                new Exception('Invalid IP address provided.')
            );

            return $this->ipAddress = $ipAddress;
        } catch (Throwable $e) {
            throw new Exception('Invalid IP address provided.');
        }
    }
}
