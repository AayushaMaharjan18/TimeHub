<?php

namespace Tests\Feature;

use App\Filament\Resources\Reviews\Pages\EditReview;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regression test for a bug where saving the review edit form in the admin
 * panel failed with "no such column: product" — the disabled, dot-notation
 * display fields (product.name, user.name, …) were being dehydrated back
 * into the save payload as literal "product"/"user"/"order" attributes.
 */
class ReviewAdminEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_a_review_without_the_display_only_relation_fields_erroring(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::create(['name' => 'Test Watch', 'slug' => 'test-watch', 'sku' => 'TW-1', 'price' => 1000, 'final_price' => 1000]);
        $customer = User::factory()->create();
        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $customer->id,
            'rating' => 4,
            'comment' => 'Great watch',
            'status' => Review::STATUS_PENDING,
        ]);

        $this->actingAs($admin);

        Livewire::test(EditReview::class, ['record' => $review->getRouteKey()])
            ->fillForm([
                'rating' => 5,
                'comment' => 'Great watch, updated',
                'status' => Review::STATUS_APPROVED,
                'is_visible_on_homepage' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $review->refresh();
        $this->assertSame(Review::STATUS_APPROVED, $review->status);
        $this->assertTrue($review->is_approved);
        $this->assertTrue($review->is_visible_on_homepage);
        $this->assertSame('Great watch, updated', $review->comment);
    }
}
