<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\WishlistItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WishlistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $items = WishlistItem::with('product.brand', 'product.categories')
            ->where('user_id', $request->user()?->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function (WishlistItem $item) {
                $product = $item->product;
                if ($product) {
                    $product->load('brand', 'categories');
                }

                return $product;
            })
            ->filter();

        // Serialize with the same ProductResource used by every other product
        // endpoint so the payload shape is consistent for the frontend.
        return response()->json([
            'data' => ProductResource::collection($items->values()),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $product = Product::findOrFail($request->product_id);

        $wishlistItem = WishlistItem::firstOrCreate([
            'user_id' => $request->user()?->id,
            'product_id' => $product->id,
        ]);

        $product->load('brand', 'categories');

        return response()->json([
            'message' => 'Product added to wishlist',
            'data' => new ProductResource($product),
        ], 201);
    }

    public function show(Request $request, Product $product): JsonResponse
    {
        $exists = WishlistItem::where('user_id', $request->user()?->id)
            ->where('product_id', $product->id)
            ->exists();

        return response()->json(['data' => ['exists' => $exists]]);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        WishlistItem::where('user_id', $request->user()?->id)
            ->where('product_id', $product->id)
            ->delete();

        return response()->json(['message' => 'Product removed from wishlist']);
    }
}
