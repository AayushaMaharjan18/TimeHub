<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Support\MediaUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) ($request->per_page ?? 9), 50);

        $blogs = Blog::published()
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'data' => $blogs->getCollection()->map(fn (Blog $b) => $this->present($b)),
            'meta' => [
                'current_page' => $blogs->currentPage(),
                'last_page' => $blogs->lastPage(),
                'total' => $blogs->total(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $blog = Blog::published()->where('slug', $slug)->first();

        if (! $blog) {
            return response()->json(['message' => 'Post not found', 'code' => 'BLOG_NOT_FOUND'], 404);
        }

        $related = Blog::published()
            ->where('id', '!=', $blog->id)
            ->orderBy('published_at', 'desc')
            ->take(3)
            ->get();

        return response()->json([
            'data' => $this->present($blog, true),
            'related' => $related->map(fn (Blog $b) => $this->present($b)),
        ]);
    }

    public static function present(Blog $blog, bool $withContent = false): array
    {
        $data = [
            'id' => $blog->id,
            'title' => $blog->title,
            'slug' => $blog->slug,
            'excerpt' => $blog->excerpt,
            'featured_image' => MediaUrl::for($blog->featured_image),
            'author' => $blog->author,
            'meta_title' => $blog->meta_title,
            'meta_description' => $blog->meta_description,
            'published_at' => $blog->published_at?->format('Y-m-d'),
        ];

        if ($withContent) {
            $data['content'] = $blog->content;
        }

        return $data;
    }
}
