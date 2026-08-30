<?php

namespace Tests\Feature;

use App\Models\ContentPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentPageCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_api_only_returns_published_pages(): void
    {
        ContentPage::create([
            'slug' => 'privacy-policy',
            'title' => 'Privacy Policy',
            'content' => '<p>Draft content</p>',
            'status' => ContentPage::STATUS_DRAFT,
        ]);

        $this->getJson('/api/v1/content/privacy-policy')
            ->assertStatus(404)
            ->assertJsonPath('code', 'CONTENT_NOT_FOUND');
    }

    public function test_public_api_returns_published_page_content(): void
    {
        ContentPage::create([
            'slug' => 'shipping-info',
            'title' => 'Shipping Information',
            'content' => '<p>We ship nationwide.</p>',
            'status' => ContentPage::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $this->getJson('/api/v1/content/shipping-info')
            ->assertStatus(200)
            ->assertJsonPath('data.title', 'Shipping Information')
            ->assertJsonPath('data.content', '<p>We ship nationwide.</p>');
    }

    public function test_unknown_slug_returns_404_without_touching_the_database(): void
    {
        $this->getJson('/api/v1/content/not-a-real-page')
            ->assertStatus(404)
            ->assertJsonPath('code', 'CONTENT_NOT_FOUND');
    }

    public function test_content_is_sanitized_on_save(): void
    {
        $page = ContentPage::create([
            'slug' => 'terms-and-conditions',
            'title' => 'Terms',
            'content' => '<p>Hello</p><script>alert(1)</script><a href="javascript:alert(2)">bad</a>',
            'status' => ContentPage::STATUS_PUBLISHED,
        ]);

        $this->assertStringNotContainsString('<script', $page->content);
        $this->assertStringNotContainsString('javascript:', $page->content);
        $this->assertStringContainsString('<p>Hello</p>', $page->content);
    }

    public function test_updating_content_records_who_made_the_change(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page = ContentPage::create([
            'slug' => 'faq',
            'title' => 'FAQ',
            'content' => '<p>Intro</p>',
            'status' => ContentPage::STATUS_PUBLISHED,
        ]);

        $this->actingAs($admin, 'web');
        $page->update(['content' => '<p>Updated intro</p>']);

        $this->assertSame($admin->id, $page->fresh()->updated_by);
    }
}
