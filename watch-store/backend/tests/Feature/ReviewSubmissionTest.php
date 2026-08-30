<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_submit_feedback_for_a_product(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Feedback Watch',
            'slug' => 'feedback-watch',
            'sku' => 'FW-001',
            'price' => 12000,
            'final_price' => 12000,
            'stock_quantity' => 5,
            'in_stock' => true,
        ]);

        $order = Order::create([
            'order_number' => 'ORD-TEST-001',
            'user_id' => $user->id,
            'subtotal' => 12000,
            'shipping_cost' => 0,
            'tax' => 0,
            'discount' => 0,
            'total' => 12000,
            'status' => 'completed',
            'payment_method' => 'cod',
            'payment_status' => 'paid',
            'shipping_name' => 'Test User',
            'shipping_phone' => '9800000000',
            'shipping_district' => 'Kathmandu',
            'shipping_municipality' => 'Kathmandu',
            'shipping_ward' => '1',
            'shipping_street' => 'Test Street',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'quantity' => 1,
            'price' => 12000,
            'total' => 12000,
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/reviews', [
            'product_id' => $product->id,
            'rating' => 5,
            'title' => 'Great experience',
            'comment' => 'Fast delivery and great quality.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Thank you for your feedback. It will be visible after approval.');

        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);
    }

    public function test_users_without_a_completed_purchase_cannot_submit_a_review(): void
    {
        $user = User::factory()->create();
        $product = Product::create([
            'name' => 'Unpurchased Watch',
            'slug' => 'unpurchased-watch',
            'sku' => 'UW-001',
            'price' => 15000,
            'final_price' => 15000,
            'stock_quantity' => 4,
            'in_stock' => true,
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/reviews', [
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'I want to leave feedback.',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('message', 'You can only leave a review after successfully purchasing this product.');

        $this->assertDatabaseCount('reviews', 0);
    }

    public function test_approved_reviews_are_returned_for_a_product(): void
    {
        $product = Product::create([
            'name' => 'Reviewable Watch',
            'slug' => 'reviewable-watch',
            'sku' => 'RW-001',
            'price' => 11000,
            'final_price' => 11000,
            'stock_quantity' => 3,
            'in_stock' => true,
        ]);

        Review::create([
            'product_id' => $product->id,
            'user_id' => User::factory()->create()->id,
            'rating' => 5,
            'title' => 'Loved it',
            'comment' => 'Excellent craftsmanship.',
            'status' => Review::STATUS_APPROVED,
        ]);

        $response = $this->getJson('/api/v1/products/' . $product->id . '/reviews');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
