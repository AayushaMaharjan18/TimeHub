<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Public list of APPROVED reviews for a single product.
     */
    public function index(Product $product): JsonResponse
    {
        $reviews = $product->reviews()
            ->with(['user', 'product', 'order'])
            ->where('status', Review::STATUS_APPROVED)
            ->latest()
            ->get();

        return response()->json([
            'data' => ReviewResource::collection($reviews),
        ]);
    }

    /**
     * Featured testimonials for the homepage: only APPROVED reviews that have
     * been explicitly marked as visible on the homepage, newest first.
     */
    public function publicIndex(): JsonResponse
    {
        $reviews = Review::query()
            ->with(['user', 'product'])
            ->where('status', Review::STATUS_APPROVED)
            ->where('is_visible_on_homepage', true)
            ->latest()
            ->take(12)
            ->get();

        return response()->json([
            'data' => ReviewResource::collection($reviews),
        ]);
    }

    /**
     * Whether the authenticated customer may review a product, plus their
     * existing review (if any). Powers the conditional "Write a Review" UI.
     */
    public function eligibility(Request $request, Product $product): JsonResponse
    {
        $user = $request->user();
        $existing = Review::where('user_id', $user?->id)
            ->where('product_id', $product->id)
            ->with(['user', 'product', 'order'])
            ->first();

        $purchased = $user !== null
            && $user->orders()
                ->whereNotIn('status', ['cancelled'])
                ->whereHas('items', fn ($query) => $query->where('product_id', $product->id))
                ->exists();

        return response()->json([
            'data' => [
                'purchased' => (bool) $purchased,
                'has_reviewed' => $existing !== null,
                'can_review' => (bool) ($purchased && $existing === null),
                'review' => $existing ? new ReviewResource($existing) : null,
            ],
        ]);
    }

    /**
     * Submit a review. Only customers who actually purchased the product may
     * review it, and only one review per customer per product is allowed.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|between:1,5',
            'title' => 'nullable|string|max:255',
            'comment' => 'required|string|min:3|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        /** @var \App\Models\User $user */
        $user = $request->user();
        $productId = (int) $request->product_id;

        // Purchase verification: the customer must have an order (that has not
        // been cancelled) containing this product.
        $eligibleOrder = $user->orders()
            ->whereNotIn('status', ['cancelled'])
            ->whereHas('items', fn ($query) => $query->where('product_id', $productId))
            ->orderByDesc('updated_at')
            ->first();

        if (! $eligibleOrder) {
            return response()->json([
                'message' => 'You can only leave a review after successfully purchasing this product.',
            ], 403);
        }

        // Duplicate prevention: one review per user + product.
        $existing = Review::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->with(['user', 'product'])
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'You have already reviewed this product. You can edit your existing review.',
                'data' => new ReviewResource($existing),
            ], 409);
        }

        $review = Review::create([
            'user_id' => $user->id,
            'product_id' => $productId,
            'order_id' => $eligibleOrder->id,
            'rating' => (int) $request->rating,
            'title' => $request->input('title'),
            'comment' => $request->comment,
            'status' => Review::STATUS_PENDING,
            'is_approved' => false,
            'is_visible_on_homepage' => false,
            'is_verified_purchase' => true,
        ]);

        return response()->json([
            'message' => 'Thank you for your feedback. It will be visible after approval.',
            'data' => new ReviewResource($review->load(['user', 'product', 'order'])),
        ], 201);
    }

    /**
     * A customer may edit their own review. Edits are re-submitted for
     * moderation (status -> pending).
     */
    public function update(Request $request, Review $review): JsonResponse
    {
        if ($request->user()?->id !== $review->user_id) {
            return response()->json(['message' => 'You cannot edit this review.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|between:1,5',
            'title' => 'nullable|string|max:255',
            'comment' => 'required|string|min:3|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $review->update([
            'rating' => (int) $request->rating,
            'title' => $request->input('title'),
            'comment' => $request->comment,
            'status' => Review::STATUS_PENDING,
            'is_approved' => false,
            'is_visible_on_homepage' => false,
        ]);

        return response()->json([
            'message' => 'Your review has been updated and is pending approval.',
            'data' => new ReviewResource($review->load(['user', 'product', 'order'])),
        ]);
    }

    /**
     * A customer may delete their own review.
     */
    public function destroy(Request $request, Review $review): JsonResponse
    {
        if ($request->user()?->id !== $review->user_id) {
            return response()->json(['message' => 'You cannot delete this review.'], 403);
        }

        $review->delete();

        return response()->json(['message' => 'Your review was deleted.']);
    }

    /**
     * The current customer's own reviews (including pending/rejected status).
     */
    public function mine(Request $request): JsonResponse
    {
        $reviews = $request->user()->reviews()
            ->with(['product', 'order'])
            ->latest()
            ->get();

        return response()->json([
            'data' => ReviewResource::collection($reviews),
        ]);
    }
}

