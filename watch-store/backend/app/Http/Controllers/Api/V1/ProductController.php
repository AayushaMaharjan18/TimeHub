<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('brand', 'categories')->where('status', 'active');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // brand / category accept ids or slugs, as an array or comma-separated list.
        if ($request->filled('brand')) {
            $brands = $this->listParam($request->brand);
            $query->whereHas('brand', function ($q) use ($brands) {
                $q->whereIn('brands.id', $brands)->orWhereIn('brands.slug', $brands);
            });
        }

        if ($request->filled('category')) {
            $categories = $this->listParam($request->category);
            $query->whereHas('categories', function ($q) use ($categories) {
                $q->whereIn('categories.id', $categories)->orWhereIn('categories.slug', $categories);
            });
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('movement')) {
            $query->where('movement', $request->movement);
        }

        if ($request->filled('min_price')) {
            $query->where('final_price', '>=', $request->min_price);
        }

        if ($request->filled('max_price')) {
            $query->where('final_price', '<=', $request->max_price);
        }

        if ($request->filled('in_stock')) {
            $query->where('in_stock', true);
        }

        if ($request->filled('is_featured')) {
            $query->where('is_featured', true);
        }

        if ($request->filled('is_new')) {
            $query->where('is_new', true);
        }

        if ($request->filled('is_best_seller')) {
            $query->where('is_best_seller', true);
        }

        if ($request->filled('is_limited_edition')) {
            $query->where('is_limited_edition', true);
        }

        $sort = $request->sort ?? 'newest';
        switch ($sort) {
            case 'price_low':
                $query->orderBy('final_price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('final_price', 'desc');
                break;
            case 'popular':
            case 'best_selling':
                $query->orderBy('reviews_count', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $perPage = min((int) ($request->per_page ?? 12), 50);
        $products = $query->paginate($perPage);

        return response()->json([
            'data' => ProductResource::collection($products),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'total' => $products->total(),
            ],
            'links' => [
                'first' => $products->url(1),
                'last' => $products->url($products->lastPage()),
                'prev' => $products->previousPageUrl(),
                'next' => $products->nextPageUrl(),
            ],
        ]);
    }

    /** @return array<int, string> */
    private function listParam(mixed $value): array
    {
        $items = is_array($value) ? $value : explode(',', (string) $value);

        return array_values(array_filter(array_map('trim', $items), fn ($v) => $v !== ''));
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::with('brand', 'categories')
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        return response()->json([
            'data' => new ProductResource($product),
        ]);
    }

    public function featured(): JsonResponse
    {
        $products = Product::with('brand', 'categories')
            ->where('status', 'active')
            ->where('is_featured', true)
            ->orderBy('created_at', 'desc')
            ->take(8)
            ->get();

        return response()->json([
            'data' => ProductResource::collection($products),
        ]);
    }

    /**
     * Related products for a given product, based on shared categories or brand.
     */
    public function related(Product $product): JsonResponse
    {
        $product->load('brand', 'categories');

        $categoryIds = $product->categories->pluck('id');

        $related = Product::with('brand', 'categories')
            ->where('status', 'active')
            ->where('id', '!=', $product->id)
            ->where(function ($q) use ($product, $categoryIds) {
                $q->whereHas('categories', function ($cq) use ($categoryIds) {
                    $cq->whereIn('categories.id', $categoryIds);
                });
                if ($product->brand_id) {
                    $q->orWhere('brand_id', $product->brand_id);
                }
            })
            ->orderBy('reviews_count', 'desc')
            ->take(8)
            ->get();

        // Fallback: when nothing shares a category/brand, show recent products.
        if ($related->isEmpty()) {
            $related = Product::with('brand', 'categories')
                ->where('status', 'active')
                ->where('id', '!=', $product->id)
                ->orderBy('created_at', 'desc')
                ->take(8)
                ->get();
        }

        return response()->json([
            'data' => ProductResource::collection($related),
        ]);
    }
}