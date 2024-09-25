<?php

namespace Bentonow\BentoLaravel\Enums;

enum Command: string
{
    case ADD_TAG = 'add_tag';
    case ADD_TAG_VIA_EVENT = 'add_tag_via_event';
    case REMOVE_TAG = 'remove_tag';
    case ADD_FIELD = 'add_field';
    case REMOVE_FIELD = 'remove_field';
    case SUBSCRIBE = 'subscribe';
    case UNSUBSCRIBE = 'unsubscribe';
    case CHANGE_EMAIL = 'change_email';
}
