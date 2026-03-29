<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
    public function index()
    {
        return response()->json(Chat::whereHas('members', fn($q) => $q->where('user_id', Auth::id()))->with('members.user')->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'member_ids' => 'required|array|min:1',
            'member_ids.*' => 'integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $chat = Chat::create(['created_by' => Auth::id()]);
        $chat->members()->sync(array_unique(array_merge($request->member_ids, [Auth::id()])));

        return response()->json($chat->load('members.user'), 201);
    }

    public function show(Chat $chat)
    {
        if (!$chat->members->contains('user_id', Auth::id())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($chat->load('members.user', 'messages'));
    }
}
