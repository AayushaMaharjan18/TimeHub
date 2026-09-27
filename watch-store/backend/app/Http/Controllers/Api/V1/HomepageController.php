<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Http\Resources\ProductResource;
use App\Models\Blog;
use App\Models\Brand;
use App\Models\HeroSlider;
use App\Models\Offer;
use App\Models\Product;
use App\Support\MediaUrl;
use Illuminate\Http\JsonResponse;

class HomepageController extends Controller
{
    public function index(): JsonResponse
    {
        $heroSliders = HeroSlider::where('is_active', true)->orderBy('sort_order')->get();

        $products = fn (string $flag, string $order = 'created_at') => Product::with('brand', 'categories')
            ->where('status', 'active')->where($flag, true)
            ->orderBy($order, 'desc')->take(8)->get();

        $brands = Brand::withCount('products')
            ->where('is_featured', true)->orderBy('name')->get();

        $latestBlogs = Blog::published()->orderBy('published_at', 'desc')->take(3)->get();

        $offers = Offer::live()->orderBy('sort_order')->get();

        return response()->json([
            'hero_sliders' => $heroSliders->map(fn ($s) => [
                'id' => $s->id,
                'title' => $s->title,
                'subtitle' => $s->subtitle,
                'description' => $s->description,
                'button_text' => $s->button_text,
                'button_url' => $s->button_url,
                'image' => MediaUrl::for($s->image),
            ]),
            'featured_products' => ProductResource::collection($products('is_featured')),
            'new_arrivals' => ProductResource::collection($products('is_new')),
            'best_sellers' => ProductResource::collection($products('is_best_seller', 'reviews_count')),
            'limited_edition' => ProductResource::collection($products('is_limited_edition')),
            'brands' => BrandResource::collection($brands),
            'latest_blogs' => $latestBlogs->map(fn (Blog $b) => BlogController::present($b)),
            'offers' => $offers->map(fn (Offer $o) => [
                'id' => $o->id,
                'label' => $o->label,
                'title' => $o->title,
                'description' => $o->description,
                'button_text' => $o->button_text,
                'button_url' => $o->button_url,
                'image' => MediaUrl::for($o->image),
            ]),
        ]);
    }
}
