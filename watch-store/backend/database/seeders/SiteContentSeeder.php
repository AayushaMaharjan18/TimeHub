<?php

namespace Database\Seeders;

use App\Models\FooterSetting;
use App\Models\Offer;
use Illuminate\Database\Seeder;

/**
 * Default copy for the admin-editable storefront content (promo banner,
 * contact page, newsletter block, About page). Only fills empty fields, so it
 * is safe to re-run without overwriting anything edited in the admin panel.
 */
class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        if (! Offer::exists()) {
            Offer::create([
                'label' => 'Special Offer',
                'title' => 'Luxury Watches at 30% Off',
                'description' => "Limited time offer on select premium timepieces. Don't miss out on this exclusive opportunity.",
                'button_text' => 'Shop the Sale',
                'button_url' => '/shop?sort=price_low',
                'is_active' => true,
                'sort_order' => 0,
            ]);
        }

        $settings = FooterSetting::first() ?? new FooterSetting(['brand_name' => 'WATCHSTORE', 'is_active' => true]);

        $defaults = [
            'business_hours' => "Sunday - Friday: 10:00 AM - 8:00 PM\nSaturday: 10:00 AM - 6:00 PM",
            'map_embed_url' => 'https://www.google.com/maps?q=Durbar+Marg,+Kathmandu&output=embed',
            'newsletter_title' => 'Join Our Newsletter',
            'newsletter_text' => 'Subscribe to receive exclusive offers, new arrivals, and insider access to limited editions.',
            'about_title' => 'Our Story',
            'about_content' => '<p>Founded in 2015, WatchStore Nepal has been the premier destination for luxury watch enthusiasts across the country. What started as a small passion project has grown into Nepal\'s most trusted retailer of authentic timepieces.</p>'
                .'<p>We believe that a watch is more than just a timekeeping device – it\'s a statement of style, a piece of engineering art, and often a cherished heirloom. That\'s why we\'re committed to offering only genuine, certified watches from the world\'s most prestigious brands.</p>'
                .'<p>Our team of watch experts shares your passion for horology and is dedicated to helping you find the perfect timepiece that matches your style and budget.</p>',
            'about_image' => 'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?w=800',
            'about_values' => [
                ['title' => 'Authenticity Guaranteed', 'description' => 'Every watch we sell is 100% authentic and comes with manufacturer warranty and certification.'],
                ['title' => 'Expert Service', 'description' => 'Our certified watchmakers provide professional maintenance and repair services.'],
                ['title' => 'Customer First', 'description' => 'Your satisfaction is our priority. We offer personalized service and support.'],
            ],
            'about_stats' => [
                ['value' => '50+', 'label' => 'Premium Brands'],
                ['value' => '10,000+', 'label' => 'Happy Customers'],
                ['value' => '8 Years', 'label' => 'In Business'],
                ['value' => '100%', 'label' => 'Authentic'],
            ],
            'about_team' => [
                ['name' => 'Rajesh Sharma', 'role' => 'Founder & CEO', 'photo' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=400'],
                ['name' => 'Priya Thapa', 'role' => 'Store Manager', 'photo' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=400'],
                ['name' => 'Bikash Gurung', 'role' => 'Master Watchmaker', 'photo' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400'],
            ],
        ];

        foreach ($defaults as $key => $value) {
            if (blank($settings->{$key})) {
                $settings->{$key} = $value;
            }
        }

        $settings->save();
    }
}
