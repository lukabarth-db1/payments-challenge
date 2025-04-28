<?php

namespace App\Helpers;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case REFUND = 'refunded';
    case CANCELED = 'canceled';
}
