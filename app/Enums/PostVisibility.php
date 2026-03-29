<?php

namespace App\Enums;

enum PostVisibility:String
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';
    case FRIENDS_ONLY = 'friends_only';
}
