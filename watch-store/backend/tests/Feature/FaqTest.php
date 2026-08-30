<?php

namespace Tests\Feature;

use App\Models\FaqCategory;
use App\Models\FaqItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_api_returns_only_published_items_grouped_by_category(): void
    {
        $category = FaqCategory::create(['name' => 'Shipping', 'slug' => 'shipping', 'sort_order' => 1]);

        FaqItem::create([
            'faq_category_id' => $category->id,
            'question' => 'Published question',
            'answer' => '<p>Answer</p>',
            'sort_order' => 1,
            'status' => FaqItem::STATUS_PUBLISHED,
        ]);

        FaqItem::create([
            'faq_category_id' => $category->id,
            'question' => 'Draft question',
            'answer' => '<p>Hidden</p>',
            'sort_order' => 2,
            'status' => FaqItem::STATUS_DRAFT,
        ]);

        $response = $this->getJson('/api/v1/faqs');

        $response->assertStatus(200);
        $items = collect($response->json('data.0.items'))->pluck('question');
        $this->assertTrue($items->contains('Published question'));
        $this->assertFalse($items->contains('Draft question'));
    }

    public function test_faq_answer_is_sanitized_on_save(): void
    {
        $item = FaqItem::create([
            'question' => 'Is this safe?',
            'answer' => '<p>Yes</p><script>alert(1)</script>',
            'status' => FaqItem::STATUS_PUBLISHED,
        ]);

        $this->assertStringNotContainsString('<script', $item->answer);
    }

    public function test_a_category_with_no_published_items_is_omitted(): void
    {
        $category = FaqCategory::create(['name' => 'Empty', 'slug' => 'empty', 'sort_order' => 1]);
        FaqItem::create([
            'faq_category_id' => $category->id,
            'question' => 'Draft only',
            'answer' => '<p>x</p>',
            'status' => FaqItem::STATUS_DRAFT,
        ]);

        $response = $this->getJson('/api/v1/faqs');

        $names = collect($response->json('data'))->pluck('name');
        $this->assertFalse($names->contains('Empty'));
    }
}
