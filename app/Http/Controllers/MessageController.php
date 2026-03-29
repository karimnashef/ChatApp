<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function index(Conversation $chat)
    {
        if (!$chat->members->contains('user_id', Auth::id())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($chat->messages()->with('user')->orderBy('created_at')->get());
    }

    public function store(Request $request, Conversation $chat)
    {
        if (!$chat->members->contains('user_id', Auth::id())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'body' => 'required_without:media|string|nullable',
            'media_url' => 'sometimes|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $message = Message::create([
            'conversation_id' => $chat->id,
            'user_id' => Auth::id(),
            'body' => $request->body,
            'media_url' => $request->media_url,
        ]);

        return response()->json($message, 201);
    }

    public function markAsRead(Message $message)
    {
        if ($message->conversation->members->contains('user_id', Auth::id())) {
            $message->read_by = array_unique(array_merge($message->read_by ?? [], [Auth::id()]));
            $message->save();
            return response()->json(['message' => 'Marked as read']);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }
}
