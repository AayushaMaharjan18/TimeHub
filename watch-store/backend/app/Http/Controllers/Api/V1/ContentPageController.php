<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ContentPage;
use Illuminate\Http\JsonResponse;

class ContentPageController extends Controller
{
    private const ALLOWED_SLUGS = [
        'customer-service',
        'faq',
        'shipping-info',
        'returns-exchanges',
        'privacy-policy',
        'terms-and-conditions',
    ];

    public function show(string $slug): JsonResponse
    {
        if (! in_array($slug, self::ALLOWED_SLUGS, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found',
                'code' => 'CONTENT_NOT_FOUND',
            ], 404);
        }

        $page = ContentPage::published()->where('slug', $slug)->first();

        if (! $page) {
            return response()->json([
                'success' => false,
                'message' => 'Page not found',
                'code' => 'CONTENT_NOT_FOUND',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'slug' => $page->slug,
                'title' => $page->title,
                'content' => $page->content,
                'updated_at' => $page->updated_at,
            ],
        ]);
    }
}
