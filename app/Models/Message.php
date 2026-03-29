<?php

namespace App\Models;

use App\Enums\MessageReactions;
use App\Enums\MessageStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'user_id',
        'body',
        'reply_to',
        'forwarded_from',
        'status'
    ];

    protected $casts = [
        'status' => MessageStatus::class,
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function replyTo()
    {
        return $this->belongsTo(Message::class, 'reply_to');
    }

    public function replies()
    {
        return $this->hasMany(Message::class, 'reply_to');
    }

    public function forwardedFrom()
    {
        return $this->belongsTo(Message::class, 'forwarded_from');
    }

    public function forwardedMessages()
    {
        return $this->hasMany(Message::class, 'forwarded_from');
    }

    public function membersRead()
    {
        return $this->belongsToMany(User::class, 'message_reads', 'message_id', 'user_id')
            ->withPivot('read_at')
            ->withTimestamps();
    }

    public function reactions()
    {
        return $this->belongsToMany(User::class, 'message_reactions', 'message_id', 'user_id')
            ->withPivot('reaction')
            ->withTimestamps();
    }
}


?>
