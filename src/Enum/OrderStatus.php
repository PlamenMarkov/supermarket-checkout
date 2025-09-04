<?php

namespace App\Enum;

enum OrderStatus: string
{
    case CREATED = 'created';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}
