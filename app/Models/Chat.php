<?php

namespace App\Models;

use App\Enums\ChatType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;

class Chat extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'created_by',
        'type',
    ];

    protected $casts = [
        'type' => ChatType::class,
    ];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class , 'chat_user')
        ->withPivot('id')
        ->withTimestamps();
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function isGroup(): bool
    {
        return $this->type === ChatType::GROUP;
    }

    public function isPrivate(): bool
    {
        return $this->type === ChatType::PRIVATE;
    }
}

