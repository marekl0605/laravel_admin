<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        $posts = Post::with('user')->get();
        return response()->json(['posts' => $posts]);
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $post = $request->user()->posts()->create($validated);
        return response()->json(['message' => 'Post created', 'post' => $post], 201);
    }

    public function show(Post $post): \Illuminate\Http\JsonResponse
    {
        return response()->json(['post' => $post->load('user')]);
    }

    public function update(Request $request, Post $post): \Illuminate\Http\JsonResponse
    {
        $this->authorize('update', $post);
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
        ]);

        $post->update($validated);
        return response()->json(['message' => 'Post updated', 'post' => $post]);
    }

    public function destroy(Post $post): \Illuminate\Http\JsonResponse
    {
        $this->authorize('delete', $post);
        $post->delete();
        return response()->json(['message' => 'Post deleted']);
    }
}