<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResources extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'bio' => $this->bio,
            'telegram_chat_id' => $this->telegram_chat_id,
            'ip' => $this->IP,
            'created_at' => $this->created_at,
        ];
    }
}
