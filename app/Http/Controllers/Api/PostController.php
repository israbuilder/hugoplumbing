<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $posts = Post::query()
            ->published()
            ->with(['category', 'tags', 'author'])
            ->latest('published_at')
            ->paginate(12);

        return PostResource::collection($posts);
    }

    public function featured(): AnonymousResourceCollection
    {
        $posts = Post::query()
            ->published()
            ->where('is_featured', true)
            ->with(['category', 'tags', 'author'])
            ->latest('published_at')
            ->limit(6)
            ->get();

        return PostResource::collection($posts);
    }

    public function show(Post $post): PostResource
    {
        abort_unless(
            $post->status === 'published'
            && $post->published_at !== null
            && $post->published_at->isPast(),
            404
        );

        return new PostResource(
            $post->load(['category', 'tags', 'author'])
        );
    }
}