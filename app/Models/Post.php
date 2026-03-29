<?php

namespace App\Models;

use App\Enums\PostVisibility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'body' , 'visibility'];

    protected $casts = [
        'visibility' => PostVisibility::class,
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comments()
    {
        return $this->hasMany(PostComment::class);
    }

    public function reactionBy()
    {
        return $this->belongsToMany(User::class, 'post_reactions')
        ->withPivot('reaction')
        ->withTimestamps();
    }
}
