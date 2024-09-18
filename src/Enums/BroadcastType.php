<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Enums;

enum BroadcastType: string
{
    case PLAIN = 'plain';

    case RAW = 'raw';
}
