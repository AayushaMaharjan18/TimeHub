<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Services\Payments\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Receives the browser redirect from eSewa/Khalti after checkout, performs
 * the authoritative server-side verification, and sends the browser on to
 * the frontend result page with nothing more than a status flag — the
 * frontend never decides success/failure itself.
 */
class PaymentCallbackController extends Controller
{
    public function __construct(private readonly PaymentService $payments)
    {
    }

    public function esewa(Request $request): RedirectResponse
    {
        $frontend = rtrim(config('app.frontend_url'), '/');

        if ($request->query('result') === 'failure' || ! $request->has('data')) {
            return redirect()->away("{$frontend}/payment/esewa?status=failed");
        }

        try {
            $transaction = $this->payments->provider('esewa')->verifyPayment([
                'data' => $request->query('data'),
            ]);

            $status = $transaction->status === PaymentTransaction::STATUS_SUCCESS ? 'success' : 'failed';
            $orderNumber = $transaction->order?->order_number;

            return redirect()->away("{$frontend}/payment/esewa?status={$status}&order=" . urlencode((string) $orderNumber));
        } catch (\Throwable $e) {
            Log::error('eSewa callback verification error', ['message' => $e->getMessage()]);
            return redirect()->away("{$frontend}/payment/esewa?status=failed");
        }
    }

    public function khalti(Request $request): RedirectResponse
    {
        $frontend = rtrim(config('app.frontend_url'), '/');
        $pidx = $request->query('pidx');

        if (! $pidx) {
            return redirect()->away("{$frontend}/payment/khalti?status=failed");
        }

        try {
            $transaction = $this->payments->provider('khalti')->verifyPayment(['pidx' => $pidx]);

            $status = $transaction->status === PaymentTransaction::STATUS_SUCCESS ? 'success' : 'failed';
            $orderNumber = $transaction->order?->order_number;

            return redirect()->away("{$frontend}/payment/khalti?status={$status}&order=" . urlencode((string) $orderNumber));
        } catch (\Throwable $e) {
            Log::error('Khalti callback verification error', ['message' => $e->getMessage()]);
            return redirect()->away("{$frontend}/payment/khalti?status=failed");
        }
    }
}
