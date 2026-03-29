<?php

namespace App\Enums;

enum MessageReactions:String
{
    case LIKE = 'like';
    case LOVE = 'love';
    case LAUGH = 'laugh';
    case SURPRISE = 'surprise';
    case SAD = 'sad';
    case ANGRY = 'angry';
}
