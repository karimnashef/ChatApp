<?php

namespace App\Http\Controllers;

use App\Models\Friend;
use App\Models\FriendRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FriendController extends Controller
{
    public function index()
    {
        // returned friend list may include accepted friend relationships
        return response()->json(Friend::where('user_id', Auth::id())->with('friend')->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipient_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        if ($request->recipient_id == Auth::id()) {
            return response()->json(['message' => 'Cannot friend yourself'], 422);
        }

        $existing = FriendRequest::where('sender_id', Auth::id())
            ->where('recipient_id', $request->recipient_id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Request already sent'], 409);
        }

        $requestModel = FriendRequest::create([
            'sender_id' => Auth::id(),
            'recipient_id' => $request->recipient_id,
            'status' => 'pending',
        ]);

        return response()->json($requestModel, 201);
    }

    public function accept(FriendRequest $friendRequest)
    {
        if ($friendRequest->recipient_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $friendRequest->status = 'accepted';
        $friendRequest->save();

        // Create two-way friendship
        Friend::firstOrCreate(['user_id' => Auth::id(), 'friend_id' => $friendRequest->sender_id]);
        Friend::firstOrCreate(['user_id' => $friendRequest->sender_id, 'friend_id' => Auth::id()]);

        return response()->json(['message' => 'Friend request accepted']);
    }

    public function decline(FriendRequest $friendRequest)
    {
        if ($friendRequest->recipient_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $friendRequest->status = 'declined';
        $friendRequest->save();

        return response()->json(['message' => 'Friend request declined']);
    }

    public function destroy(Friend $friend)
    {
        if ($friend->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $friend->delete();
        return response()->json(['message' => 'Friend removed']);
    }

    public function requests()
    {
        return response()->json(FriendRequest::where('recipient_id', Auth::id())->with('sender')->get());
    }
}

