<?php

return [

    // Meta WhatsApp Cloud API. All values must come from environment variables —
    // never hardcode a real access token or app secret here or anywhere in source.
    'access_token' => env('WHATSAPP_ACCESS_TOKEN', ''),
    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID', ''),
    'business_account_id' => env('WHATSAPP_BUSINESS_ACCOUNT_ID', ''),

    // Shared secret the frontend/Meta webhook verification handshake must present.
    'verify_token' => env('WHATSAPP_VERIFY_TOKEN', ''),

    // Optional: Meta app secret, used to validate the X-Hub-Signature-256 header
    // on incoming webhook deliveries. Verification is skipped (with a logged
    // warning) when this is blank, which is only acceptable in local development.
    'app_secret' => env('WHATSAPP_APP_SECRET', ''),

    'api_version' => env('WHATSAPP_API_VERSION', 'v21.0'),

    'base_url' => env('WHATSAPP_BASE_URL', 'https://graph.facebook.com'),

    // Notify the customer by WhatsApp when an order's status changes.
    'notify_on_status_change' => env('WHATSAPP_NOTIFY_ON_STATUS_CHANGE', true),

];
