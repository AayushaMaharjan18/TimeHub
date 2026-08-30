# WatchStore Nepal — Backend

Laravel 12 + Filament 5 API and admin panel for the WatchStore Nepal e-commerce site. The frontend is a separate Nuxt 3 app in `../frontend`.

## Local development

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8000
```

API base: `http://127.0.0.1:8000/api/v1`
Admin panel: `http://127.0.0.1:8000/admin` — seeded admin login is `admin@watchstore.com` / `admin123` (change this before deploying).

Frontend (in `../frontend`):

```bash
npm install
npm run dev
```

The frontend expects the API at `NUXT_PUBLIC_API_BASE_URL` (default `http://localhost:8000/api`) and reads the backend's `FRONTEND_URL` env var for payment-gateway redirects, so keep both in sync — see **Environment variables** below.

## Testing

```bash
php artisan test
```

Payment provider tests mock the gateway HTTP calls (`Http::fake`), so they run without real eSewa/Khalti credentials. They cover: successful verification, invalid signature, tampered amount, wrong amount, pending/cancelled status, and duplicate/idempotent verification.

## Environment variables

Copy `.env.example` to `.env` and fill in the sections below as needed. Nothing here is committed to source control, and no real secret has a working default — the only defaults provided are publicly documented sandbox values (eSewa's UAT secret, Khalti's test public key format) that are safe to share.

| Variable | Purpose |
|---|---|
| `FRONTEND_URL` | Nuxt storefront origin. The payment callback controllers redirect the browser here after verifying with the gateway. |
| `ESEWA_ENVIRONMENT` | `test` (default, UAT sandbox) or `production`. |
| `ESEWA_MERCHANT_ID` | eSewa product/merchant code. `EPAYTEST` for sandbox. |
| `ESEWA_SECRET_KEY` | HMAC signing secret for the v2 form API. The UAT value is eSewa's own published test secret — replace it with your real merchant secret in production. |
| `ESEWA_GATEWAY_URL` / `ESEWA_STATUS_CHECK_URL` | Only needed to override eSewa's standard endpoints for the chosen environment. |
| `KHALTI_ENVIRONMENT` | `test` (dev.khalti.com sandbox) or `production`. |
| `KHALTI_PUBLIC_KEY` | Public key (safe for the frontend, currently unused server-side but kept for reference). |
| `KHALTI_SECRET_KEY` | Server-side secret from your Khalti merchant dashboard. **Required for live payments** — with this blank, `POST /v1/payments/initiate` for Khalti returns a clear "not configured" error instead of a fake success. |
| `WHATSAPP_ACCESS_TOKEN`, `WHATSAPP_PHONE_NUMBER_ID`, `WHATSAPP_BUSINESS_ACCOUNT_ID` | Meta WhatsApp Cloud API credentials, from your Meta App / WhatsApp Business Platform setup. |
| `WHATSAPP_VERIFY_TOKEN` | Shared secret you invent; enter the same value when configuring the webhook in the Meta dashboard. |
| `WHATSAPP_APP_SECRET` | Meta app secret, used to verify the `X-Hub-Signature-256` header on incoming webhooks. Recommended once the token/phone ID are set. |
| `WHATSAPP_API_VERSION` | Graph API version, e.g. `v21.0`. |

Leaving the WhatsApp variables blank does **not** disable the storefront's WhatsApp buttons — those are plain `wa.me` click-to-chat links built from the WhatsApp number set in **Admin → Footer → Contact Information**, which needs no API credentials at all. The Cloud API credentials are only needed for the backend-triggered order-status notifications and the incoming-message webhook.

## Payments: how verification actually works

Both providers follow the same shape: **frontend asks the backend to start a payment → backend talks to the gateway → gateway redirects the browser back to a backend route → backend verifies server-to-server → backend redirects to a frontend result page with just a status flag.** At no point does the frontend get to decide "this payment succeeded" — see `app/Services/Payments/EsewaProvider.php` and `KhaltiProvider.php`.

1. `POST /api/v1/payments/initiate` (auth required) — takes `{order_id, provider}`, creates a `PaymentTransaction` row, returns gateway-specific instructions (a signed form for eSewa, a `payment_url` for Khalti).
2. The frontend submits the eSewa form / redirects to the Khalti URL.
3. The gateway redirects the browser to `GET /payments/{esewa,khalti}/callback` (in `routes/web.php`, handled by `PaymentCallbackController`).
4. That controller calls the provider's `verifyPayment()`, which:
   - eSewa: verifies the HMAC signature on the redirect payload, checks the amount, then independently calls eSewa's transaction-status API before marking anything paid.
   - Khalti: calls Khalti's `/epayment/lookup/` endpoint with the `pidx` and checks `status === 'Completed'` and the amount, before marking anything paid.
   - Both are idempotent — verifying an already-`success` transaction again is a no-op and does not re-call the gateway.
5. The controller redirects the browser to `{FRONTEND_URL}/payment/{esewa,khalti}?status=success|failed&order=...`, and that page just displays the result.

Order pricing itself is never trusted from the client either: `OrderController::store` only accepts `product_id` + `quantity` per line item and re-derives price, name, and totals from the `products` table and the shipping district's cost.

## Order tracking

- `POST /api/v1/orders/track` (public) — takes `{order_number, phone}`; both must match, so an order can't be looked up by number alone.
- `GET /api/v1/orders/{order}` (auth) — for a logged-in user viewing their own order; 404s for anyone else's order id.
- Every status change (via the API, a payment verification, or the admin panel) is recorded in `order_status_histories` automatically — see `Order::booted()` in `app/Models/Order.php`. A `delivered`/`cancelled`/`returned`/`refunded` order cannot be moved to a different status; the guard lives in the same model event, so it applies no matter which code path tries to change it.
- Frontend page: `/track-order`.

## CMS content pages & FAQ

Six fixed pages (`customer-service`, `faq`, `shipping-info`, `returns-exchanges`, `privacy-policy`, `terms-and-conditions`) are stored in the `content_pages` table and edited from **Admin → Content → Customer Service Pages**. `GET /api/v1/content/{slug}` only ever returns `published` content. HTML is sanitized on save (`App\Support\HtmlSanitizer`, an allowlist-based cleaner — no `<script>`/`<iframe>`, no `on*` handlers, no `javascript:`/`data:` links).

FAQ is structured data instead of one blob of HTML — `faq_categories` and `faq_items`, editable from **Admin → Content → FAQ Categories / FAQ Items**, served from `GET /api/v1/faqs` grouped by category, published items only.

The frontend pages for all of the above now fetch from these endpoints instead of the hardcoded copy they used to ship with; see `frontend/components/ContentCmsPage.vue` and `frontend/pages/faq.vue`.

## WhatsApp

- **Storefront buttons** (order support, product enquiry, general contact — a floating button on every page): plain `wa.me` links built client-side from the WhatsApp number in Footer Settings. No API token involved, nothing to configure beyond that phone number.
- **Cloud API webhook**: `GET/POST /api/v1/whatsapp/webhook`. The `GET` handles Meta's verification handshake; the `POST` receives incoming messages/delivery statuses, verifies the `X-Hub-Signature-256` signature when `WHATSAPP_APP_SECRET` is set, and always acknowledges with 200 quickly (Meta retries aggressively on non-200).
- **Outgoing order-status notifications**: `App\Jobs\SendOrderStatusWhatsAppNotification`, dispatched automatically whenever an order's status changes (see `Order::booted()`), sent via `App\Services\WhatsAppService`. It's a documented no-op when the Cloud API isn't configured, so the rest of the app works without a WhatsApp Business account attached.

## Admin order management

**Admin → Orders** — searchable/filterable by status, payment status, and payment method; edit page exposes `tracking_number` and `courier_name`, the full order status lifecycle, and a read-only **Status History** tab. Deleting the order status history rows isn't possible from the UI — the audit trail is written by the model, not hand-edited.

## API error format

Every uncaught exception under `/api/*` is rendered as a consistent JSON body (`bootstrap/app.php`):

```json
{ "success": false, "message": "...", "code": "SOME_CODE" }
```

Stack traces, gateway payloads, and other internals are never included, regardless of `APP_DEBUG`.

## What still needs real credentials before going live

- **eSewa**: production merchant ID + secret key from eSewa's merchant onboarding (the sandbox works out of the box with the defaults above).
- **Khalti**: a live secret key from your Khalti merchant dashboard.
- **WhatsApp Cloud API**: a Meta Business/WhatsApp Business Platform app, a verified phone number, and the four `WHATSAPP_*` values above — needed only for the Cloud API notifications/webhook, not for the storefront's click-to-chat buttons.
- **Production domain**: set `APP_URL` and `FRONTEND_URL` to your real domains so payment callback redirects land in the right place.
