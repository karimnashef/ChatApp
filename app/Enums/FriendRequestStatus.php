<?php

namespace App\Enums;

enum FriendRequestStatus:String
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
}
