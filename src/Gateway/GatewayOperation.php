<?php

namespace App\Gateway;

enum GatewayOperation: string
{
    case CREATE = 'create';
    case CONFIRM = 'confirm';
    case CANCEL = 'cancel';
    case REFUND = 'refund';
}
