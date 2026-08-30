<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(array $overrides = []): Order
    {
        return Order::create(array_merge([
            'order_number' => 'ORD-TRACK1',
            'subtotal' => 1000,
            'shipping_cost' => 0,
            'tax' => 0,
            'discount' => 0,
            'total' => 1000,
            'status' => Order::STATUS_PENDING,
            'payment_method' => 'cod',
            'payment_status' => Order::PAYMENT_STATUS_UNPAID,
            'shipping_name' => 'Jane Doe',
            'shipping_phone' => '9811112222',
            'shipping_district' => 'Kathmandu',
            'shipping_municipality' => 'Kathmandu',
            'shipping_ward' => '5',
            'shipping_street' => 'Test Street',
        ], $overrides));
    }

    public function test_order_creation_writes_an_initial_status_history_entry(): void
    {
        $order = $this->makeOrder();

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'status' => Order::STATUS_PENDING,
        ]);
    }

    public function test_status_transition_appends_a_history_entry_and_can_notify(): void
    {
        $order = $this->makeOrder();

        $order->recordStatusChange(Order::STATUS_SHIPPED, 'Handed to courier.');

        $this->assertSame(Order::STATUS_SHIPPED, $order->fresh()->status);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'status' => Order::STATUS_SHIPPED,
            'note' => 'Handed to courier.',
        ]);
        $this->assertSame(2, $order->statusHistories()->count());
    }

    public function test_a_delivered_order_cannot_be_moved_to_a_different_status(): void
    {
        $order = $this->makeOrder();
        $order->recordStatusChange(Order::STATUS_DELIVERED);

        $this->expectException(\App\Exceptions\InvalidOrderStatusTransitionException::class);
        $order->recordStatusChange(Order::STATUS_SHIPPED);
    }

    public function test_public_tracking_requires_matching_order_number_and_phone(): void
    {
        $order = $this->makeOrder();

        $response = $this->postJson('/api/v1/orders/track', [
            'order_number' => $order->order_number,
            'phone' => '0000000000',
        ]);

        $response->assertStatus(404)->assertJsonPath('code', 'ORDER_NOT_FOUND');
    }

    public function test_public_tracking_returns_the_order_and_its_status_history(): void
    {
        $order = $this->makeOrder();
        $order->recordStatusChange(Order::STATUS_CONFIRMED, 'Payment received.');

        $response = $this->postJson('/api/v1/orders/track', [
            'order_number' => $order->order_number,
            'phone' => $order->shipping_phone,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.order_number', $order->order_number)
            ->assertJsonPath('data.status_history.1.status', Order::STATUS_CONFIRMED);
    }

    public function test_a_user_cannot_view_another_users_order_by_id(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $order = $this->makeOrder(['user_id' => $owner->id]);

        $response = $this->actingAs($intruder, 'sanctum')->getJson("/api/v1/orders/{$order->id}");

        $response->assertStatus(404);
    }

    public function test_order_placement_never_trusts_client_supplied_price(): void
    {
        $user = User::factory()->create();
        $product = \App\Models\Product::create([
            'name' => 'Guarded Watch',
            'slug' => 'guarded-watch',
            'sku' => 'GW-001',
            'price' => 20000,
            'final_price' => 20000,
            'stock_quantity' => 5,
            'in_stock' => true,
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/orders', [
            'shipping_name' => 'Attacker',
            'shipping_phone' => '9800000001',
            'shipping_district' => 'Kathmandu',
            'shipping_municipality' => 'Kathmandu',
            'shipping_street' => 'Test',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1, 'price' => 1, 'total' => 1],
            ],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('orders', ['id' => $response->json('data.id'), 'subtotal' => 20000, 'total' => 20000]);
        $this->assertDatabaseHas('order_items', ['product_id' => $product->id, 'price' => 20000, 'total' => 20000]);
    }
}
