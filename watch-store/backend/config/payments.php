<?php

return [

    'esewa' => [
        // 'test' uses eSewa's public UAT sandbox; 'production' requires real merchant credentials.
        'environment' => env('ESEWA_ENVIRONMENT', 'test'),

        // Merchant/product code. eSewa's published UAT code is EPAYTEST; production
        // requires the merchant code issued by eSewa during onboarding.
        'merchant_id' => env('ESEWA_MERCHANT_ID', 'EPAYTEST'),

        // HMAC-SHA256 signing secret for the v2 form API. eSewa publishes a fixed
        // UAT secret for sandbox testing; production requires the secret issued
        // by eSewa for the live merchant account. Never reuse the UAT secret in production.
        'secret_key' => env('ESEWA_SECRET_KEY', '8gBm/:&EnhH.1/q'),

        'gateway_url' => env(
            'ESEWA_GATEWAY_URL',
            env('ESEWA_ENVIRONMENT', 'test') === 'production'
                ? 'https://epay.esewa.com.np/api/epay/main/v2/form'
                : 'https://rc-epay.esewa.com.np/api/epay/main/v2/form'
        ),

        'status_check_url' => env(
            'ESEWA_STATUS_CHECK_URL',
            env('ESEWA_ENVIRONMENT', 'test') === 'production'
                ? 'https://epay.esewa.com.np/api/epay/transaction/status/'
                : 'https://rc.esewa.com.np/api/epay/transaction/status/'
        ),
    ],

    'khalti' => [
        'environment' => env('KHALTI_ENVIRONMENT', 'test'),

        // Public key is safe to expose to the client-side checkout widget.
        'public_key' => env('KHALTI_PUBLIC_KEY', ''),

        // Secret key authenticates server-to-server calls. Must never reach the frontend.
        // No default is provided — a blank secret disables live Khalti payments and the
        // provider returns a clear "not configured" error instead of faking success.
        'secret_key' => env('KHALTI_SECRET_KEY', ''),

        'base_url' => env(
            'KHALTI_BASE_URL',
            env('KHALTI_ENVIRONMENT', 'test') === 'production'
                ? 'https://khalti.com/api/v2'
                : 'https://dev.khalti.com/api/v2'
        ),
    ],

];
