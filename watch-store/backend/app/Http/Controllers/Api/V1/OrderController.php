<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingDistrict;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    private const STATUS_LABELS = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'processing' => 'Processing',
        'packed' => 'Packed',
        'shipped' => 'Shipped',
        'out_for_delivery' => 'Out for Delivery',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
        'return_requested' => 'Return Requested',
        'returned' => 'Returned',
        'refunded' => 'Refunded',
    ];

    public function index(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->where('user_id', $request->user()?->id)
            ->with('items.product')
            ->latest()
            ->get();

        return response()->json([
            'data' => $orders->map(fn (Order $order) => $this->present($order)),
        ]);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        if ($order->user_id !== $request->user()?->id) {
            return response()->json(['success' => false, 'message' => 'Order not found', 'code' => 'ORDER_NOT_FOUND'], 404);
        }

        $order->load(['items.product', 'statusHistories']);

        return response()->json(['data' => $this->present($order, true)]);
    }

    /**
     * Public order tracking: order number + the phone number on the order is
     * enough to prove you placed it, without requiring an account. No other
     * customer's order can be looked up without both pieces matching.
     */
    public function track(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_number' => 'required|string',
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $order = Order::where('order_number', $request->string('order_number'))
            ->where('shipping_phone', $request->string('phone'))
            ->with(['items.product', 'statusHistories'])
            ->first();

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'No order found matching that order number and phone number.',
                'code' => 'ORDER_NOT_FOUND',
            ], 404);
        }

        return response()->json(['success' => true, 'data' => $this->present($order, true)]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'shipping_name' => 'required|string|max:255',
            'shipping_phone' => 'required|string|max:20',
            'shipping_district' => 'required|string|max:255',
            'shipping_municipality' => 'required|string|max:255',
            'shipping_street' => 'required|string|max:255',
            'shipping_ward' => 'nullable|string|max:50',
            'payment_method' => 'nullable|string|in:cod,khalti,esewa',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // The server is the only source of truth for pricing. Only product_id
        // and quantity are trusted from the client; price/name/total are
        // always re-derived here, never taken from the request body.
        $productIds = collect($request->items)->pluck('product_id')->unique();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($request->items as $item) {
            $product = $products->get($item['product_id']);
            if (! $product || ! $product->in_stock || $product->stock_quantity < (int) $item['quantity']) {
                return response()->json([
                    'message' => "Product '{$product?->name}' is not available in the requested quantity.",
                    'code' => 'PRODUCT_UNAVAILABLE',
                ], 422);
            }
        }

        $shippingDistrict = ShippingDistrict::where('name', $request->shipping_district)->first();
        $shippingCost = $shippingDistrict->cost ?? 0;

        $order = DB::transaction(function () use ($request, $products, $shippingCost) {
            $subtotal = 0;
            $lineItems = [];

            foreach ($request->items as $item) {
                $product = $products->get($item['product_id']);
                $quantity = (int) $item['quantity'];
                $lineTotal = (float) $product->final_price * $quantity;
                $subtotal += $lineTotal;

                $lineItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $quantity,
                    'price' => $product->final_price,
                    'total' => $lineTotal,
                ];
            }

            $total = $subtotal + $shippingCost;

            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'user_id' => $request->user()?->id,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'tax' => 0,
                'discount' => 0,
                'total' => $total,
                'status' => Order::STATUS_PENDING,
                'payment_method' => $request->payment_method ?? 'cod',
                'payment_status' => $request->payment_method === 'cod' || ! $request->payment_method
                    ? Order::PAYMENT_STATUS_UNPAID
                    : Order::PAYMENT_STATUS_PENDING,
                'shipping_name' => $request->shipping_name,
                'shipping_phone' => $request->shipping_phone,
                'shipping_district' => $request->shipping_district,
                'shipping_municipality' => $request->shipping_municipality,
                'shipping_street' => $request->shipping_street,
                'shipping_ward' => $request->shipping_ward ?? '',
            ]);

            foreach ($lineItems as $line) {
                OrderItem::create($line + ['order_id' => $order->id]);
                $products->get($line['product_id'])->decrement('stock_quantity', $line['quantity']);
            }

            return $order;
        });

        return response()->json([
            'message' => 'Order placed successfully',
            'data' => $this->present($order->load('items')),
        ], 201);
    }

    private function present(Order $order, bool $withHistory = false): array
    {
        $data = $order->toArray();
        $data['status_label'] = self::STATUS_LABELS[$order->status] ?? ucfirst(str_replace('_', ' ', $order->status));

        if ($withHistory) {
            $data['status_history'] = $order->statusHistories->map(fn ($h) => [
                'status' => $h->status,
                'label' => self::STATUS_LABELS[$h->status] ?? ucfirst(str_replace('_', ' ', $h->status)),
                'note' => $h->note,
                'created_at' => $h->created_at,
            ]);
        }

        return $data;
    }
}
