<?php

namespace App\Enums;

enum MessageStatus:String
{
    case SENT = 'sent';
    case DELIVERED = 'delivered';
    case READ = 'read';
}
