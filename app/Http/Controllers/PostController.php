<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function index()
    {
        return response()->json(Post::with('author', 'likes', 'comments')->paginate(15));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'body' => 'required|string',
            'media_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $post = Post::create([
            'user_id' => Auth::id(),
            'body' => $request->body,
            'media_url' => $request->media_url,
        ]);

        return response()->json($post, 201);
    }

    public function show(Post $post)
    {
        return response()->json($post->load('author', 'likes', 'comments'));
    }

    public function update(Request $request, Post $post)
    {
        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'body' => 'sometimes|string',
            'media_url' => 'sometimes|nullable|url',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $post->fill($request->only(['body', 'media_url']));
        $post->save();

        return response()->json($post);
    }

    public function destroy(Post $post)
    {
        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->delete();
        return response()->json(['message' => 'Post deleted']);
    }

    public function like(Post $post)
    {
        $post->likes()->syncWithoutDetaching([Auth::id()]);
        return response()->json(['message' => 'Liked']);
    }

    public function unlike(Post $post)
    {
        $post->likes()->detach(Auth::id());
        return response()->json(['message' => 'Unliked']);
    }
}
