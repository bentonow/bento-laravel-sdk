<?php

declare(strict_types=1);

namespace Bentonow\BentoLaravel\Enums;

enum BentoEvent: string
{
    case PURCHASE = '$purchase';
    case SUBSCRIBE = '$subscribe';
    case TAG = '$tag';
    case REMOVE_TAG = '$remove_tag';
    case UNSUBSCRIBE = '$unsubscribe';
    case UPDATE_FIELDS = '$update_fields';
}
