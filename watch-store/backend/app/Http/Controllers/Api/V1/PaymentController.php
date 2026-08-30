<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Payments\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use RuntimeException;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments)
    {
    }

    /**
     * Initiate a payment for an order the authenticated user owns. Returns
     * gateway-specific redirect instructions; never marks anything paid here.
     */
    public function initiate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer',
            'provider' => 'required|string|in:esewa,khalti',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $order = Order::where('id', $request->integer('order_id'))
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
                'code' => 'ORDER_NOT_FOUND',
            ], 404);
        }

        if ($order->payment_status === Order::PAYMENT_STATUS_PAID) {
            return response()->json([
                'success' => false,
                'message' => 'This order has already been paid.',
                'code' => 'ORDER_ALREADY_PAID',
            ], 409);
        }

        try {
            $provider = $this->payments->provider($request->string('provider'));
            $data = $provider->createPayment($order);

            return response()->json(['success' => true, 'data' => $data]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'code' => 'PAYMENT_INITIATION_FAILED',
            ], 422);
        }
    }

    /**
     * Client-triggered re-check for the Khalti flow (the frontend polls this
     * after the backend callback redirect lands it back on /payment/khalti).
     */
    public function khaltiVerify(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pidx' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        return $this->verify('khalti', ['pidx' => $request->string('pidx')->toString()]);
    }

    public function esewaVerify(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        return $this->verify('esewa', ['data' => $request->string('data')->toString()]);
    }

    private function verify(string $providerName, array $payload): JsonResponse
    {
        try {
            $provider = $this->payments->provider($providerName);
            $transaction = $provider->verifyPayment($payload);

            $success = $transaction->status === \App\Models\PaymentTransaction::STATUS_SUCCESS;

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Payment verified' : 'Payment could not be verified',
                'data' => [
                    'status' => $transaction->status,
                    'order' => $transaction->order()->select(['id', 'order_number', 'status', 'payment_status', 'total'])->first(),
                ],
            ], $success ? 200 : 422);
        } catch (InvalidArgumentException|RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'code' => 'PAYMENT_VERIFICATION_FAILED',
            ], 422);
        }
    }
}
