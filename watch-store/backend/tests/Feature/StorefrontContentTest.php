<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Blog;
use App\Models\Brand;
use App\Models\FooterSetting;
use App\Models\Offer;
use App\Models\Product;
use App\Models\ShippingDistrict;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Endpoints behind admin-managed storefront content and customer forms.
 */
class StorefrontContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_returns_only_live_offers(): void
    {
        Offer::create(['title' => 'Live sale', 'is_active' => true]);
        Offer::create(['title' => 'Disabled', 'is_active' => false]);
        Offer::create(['title' => 'Expired', 'is_active' => true, 'ends_at' => now()->subDay()]);
        Offer::create(['title' => 'Scheduled', 'is_active' => true, 'starts_at' => now()->addDay()]);

        $this->getJson('/api/v1/homepage')
            ->assertOk()
            ->assertJsonCount(1, 'offers')
            ->assertJsonPath('offers.0.title', 'Live sale');
    }

    public function test_blog_index_hides_drafts_and_future_posts(): void
    {
        Blog::create(['title' => 'Published', 'slug' => 'published', 'status' => 'published', 'published_at' => now()->subDay()]);
        Blog::create(['title' => 'Draft', 'slug' => 'draft', 'status' => 'draft', 'published_at' => now()->subDay()]);
        Blog::create(['title' => 'Scheduled', 'slug' => 'scheduled', 'status' => 'published', 'published_at' => now()->addDay()]);

        $this->getJson('/api/v1/blogs')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.slug', 'published');
        $this->getJson('/api/v1/blogs/published')->assertOk()->assertJsonPath('data.title', 'Published');
        $this->getJson('/api/v1/blogs/draft')->assertNotFound();
    }

    public function test_settings_expose_about_page_and_contact_fields(): void
    {
        FooterSetting::create([
            'brand_name' => 'WATCHSTORE',
            'is_active' => true,
            'business_hours' => 'Sun-Fri 10-8',
            'about_stats' => [['value' => '50+', 'label' => 'Brands']],
            'about_team' => [['name' => 'Asha', 'role' => 'Owner', 'photo' => 'team/asha.jpg']],
        ]);

        $this->getJson('/api/v1/settings')
            ->assertOk()
            ->assertJsonPath('business_hours', 'Sun-Fri 10-8')
            ->assertJsonPath('about.stats.0.value', '50+')
            ->assertJsonPath('about.team.0.photo', url('storage/team/asha.jpg'));
    }

    public function test_shipping_districts_exclude_inactive(): void
    {
        ShippingDistrict::create(['name' => 'Kathmandu', 'cost' => 100]);
        ShippingDistrict::create(['name' => 'Closed', 'cost' => 100, 'is_active' => false]);

        $this->getJson('/api/v1/shipping/districts')->assertOk()->assertJsonCount(1, 'data');
    }

    public function test_products_can_be_filtered_by_brand_slug(): void
    {
        $rolex = Brand::create(['name' => 'Rolex', 'slug' => 'rolex']);
        $omega = Brand::create(['name' => 'Omega', 'slug' => 'omega']);
        Product::create(['name' => 'Sub', 'slug' => 'sub', 'sku' => 'A', 'price' => 1, 'final_price' => 1, 'brand_id' => $rolex->id]);
        Product::create(['name' => 'Speedy', 'slug' => 'speedy', 'sku' => 'B', 'price' => 1, 'final_price' => 1, 'brand_id' => $omega->id]);

        $this->getJson('/api/v1/products?brand=rolex')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson("/api/v1/products?brand=rolex,{$omega->id}")->assertOk()->assertJsonCount(2, 'data');
    }

    public function test_contact_form_is_stored_and_validated(): void
    {
        $this->postJson('/api/v1/contact', ['name' => 'A'])
            ->assertStatus(422)
            ->assertJsonPath('message', 'The email field is required.');

        $this->postJson('/api/v1/contact', ['name' => 'A', 'email' => 'a@example.test', 'message' => 'Hi'])->assertCreated();
        $this->assertDatabaseHas('contact_messages', ['email' => 'a@example.test', 'is_read' => false]);
    }

    public function test_newsletter_subscription_is_idempotent(): void
    {
        $this->postJson('/api/v1/newsletter', ['email' => 'Fan@Example.test'])->assertCreated();
        $this->postJson('/api/v1/newsletter', ['email' => 'fan@example.test'])->assertCreated();

        $this->assertDatabaseCount('newsletter_subscribers', 1);
    }

    public function test_addresses_are_scoped_to_their_owner_and_keep_one_default(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $payload = ['full_name' => 'A', 'phone' => '9800000000', 'district' => 'Kathmandu', 'municipality' => 'KMC', 'street' => 'X'];

        $first = $this->postJson('/api/v1/user/addresses', $payload)->assertCreated()->json('data');
        $this->assertTrue($first['is_default']);

        $second = $this->postJson('/api/v1/user/addresses', $payload + ['is_default' => true])->json('data');
        $this->assertFalse(Address::find($first['id'])->is_default);

        $this->deleteJson("/api/v1/user/addresses/{$second['id']}")->assertOk();
        $this->assertTrue(Address::find($first['id'])->is_default);

        Sanctum::actingAs(User::factory()->create());
        $this->deleteJson("/api/v1/user/addresses/{$first['id']}")->assertNotFound();
        $this->getJson('/api/v1/user/addresses')->assertOk()->assertJsonCount(0, 'data');
    }
}
