<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FooterSetting;
use App\Support\MediaUrl;
use Illuminate\Http\JsonResponse;

/**
 * Site-wide settings (footer, contact details, newsletter copy, About page).
 * Served at both /v1/footer (original name) and /v1/settings.
 */
class FooterController extends Controller
{
    public function index(): JsonResponse
    {
        $s = FooterSetting::where('is_active', true)->first() ?? new FooterSetting([
            'brand_name' => 'WATCHSTORE',
            'brand_description' => 'Your premier destination for luxury watches in Nepal.',
        ]);

        return response()->json([
            'brand_name' => $s->brand_name,
            'brand_description' => $s->brand_description,
            'facebook_url' => $s->facebook_url,
            'instagram_url' => $s->instagram_url,
            'twitter_url' => $s->twitter_url,
            'quick_links' => $s->quick_links ?? [],
            'customer_service_links' => $s->customer_service_links ?? [],
            'phone' => $s->phone,
            'email' => $s->email,
            'address' => $s->address,
            'copyright_text' => $s->copyright_text,
            'payment_methods' => $s->payment_methods ?? [],
            'whatsapp_number' => $s->whatsapp_number,
            'business_hours' => $s->business_hours,
            'map_embed_url' => $s->map_embed_url,
            'newsletter_title' => $s->newsletter_title,
            'newsletter_text' => $s->newsletter_text,
            'about' => [
                'title' => $s->about_title,
                'content' => $s->about_content,
                'image' => MediaUrl::for($s->about_image),
                'values' => $s->about_values ?? [],
                'stats' => $s->about_stats ?? [],
                'team' => collect($s->about_team ?? [])->map(fn ($m) => [
                    'name' => $m['name'] ?? '',
                    'role' => $m['role'] ?? '',
                    'photo' => MediaUrl::for($m['photo'] ?? null),
                ])->values(),
            ],
        ]);
    }
}
