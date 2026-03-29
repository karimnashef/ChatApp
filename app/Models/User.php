<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Chat;
use App\Models\Message;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\FriendRequest;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'bio',
        'key',
        'role',
        'telegram_chat_id',
        'ip'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function chats()
    {
        return $this->belongsToMany(Chat::class, 'chat_user')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'user_id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function postComments()
    {
        return $this->hasMany(PostComment::class);
    }

    public function reactionsPost()
    {
        return $this->belongsToMany(Post::class, 'post_reactions')
            ->withPivot('reaction')
            ->withTimestamps();
    }

    public function commentReactions()
    {
        return $this->belongsToMany(PostComment::class, 'post_comment_reactions')
            ->withPivot('reaction')
            ->withTimestamps();
    }

    public function messageReactions()
    {
        return $this->belongsToMany(Message::class, 'message_reactions')
            ->withPivot('reaction')
            ->withTimestamps();
    }

    public function friends()
    {
        return $this->hasMany(FriendRequest::class)
            ->where('status', 'accepted');
    }

    public function friendRequests()
    {
        return $this->hasMany(FriendRequest::class, 'user_id')
            ->where('status', 'pending');
    }
}
