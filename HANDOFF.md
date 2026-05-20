# BLOMFREE — Project Handoff

A high-level orientation for anyone (human or AI) picking this codebase up
fresh. Read this first, then drop into the topic-specific docs:

- `ADMIN.md` — admin user management (the `/admin` Filament panel)
- `ADMIN_AFFILIATE.md` — affiliate program ops
- `ADMIN_INSTALLMENTS.md` — installment program ops
- `BRAND.md` — brand voice + visual tokens
- `DEPLOYMENT.md` + `DEPLOY-README.txt` — production deploy notes
- `GO_LIVE_CHECKLIST.md` — pre-launch checklist

## What this is

**BLOMFREE & CO. NIG. LTD** is a Nigerian multi-subsidiary commerce site,
delivered as a single Laravel app with four customer-facing storefronts:

| Subsidiary               | Sells                                | Top-level route        |
| ------------------------ | ------------------------------------ | ---------------------- |
| Estates & Properties     | Land plots                           | `/lands`               |
| Kennel & Farm            | Dogs, cats, rabbits, grasscutters    | `/kennel-farm`         |
| Collections              | Unisex clothing                      | `/collections`         |
| Gadgets & Accessories    | Devices                              | `/gadgets`             |

Built across **16 phases**. Repo: `johnwickfuo/blomfree`, default branch
`main`.

## Tech stack

- **Laravel 11** + **Filament v3** admin panel
- **Inertia.js + Vue 3 + TypeScript + Tailwind CSS v3** for the storefront
- **Spatie Media Library** (responsive image conversions)
- **Paystack + Flutterwave** hosted checkout (test mode initially)
- **MariaDB/MySQL** in production; **SQLite** locally
- **barryvdh/laravel-dompdf** for receipts + statements
- **PHP 8.2–8.3** (Symfony was downgraded v8 → v7 to match production target)

## Architecture decisions worth remembering

- **Polymorphic relationships** for: `Inspection.inspectable`,
  `CartItem.cartable`, `OrderItem.orderable`, `InstallmentPlan.installable`.
- **Session-based cart** for one-time purchase (guest, no account needed).
- **Customer accounts via Breeze** (re-enabled in Phase 15) only used for
  installment + affiliate dashboards. The cart/checkout remain guest.
- **Three role flags on `User`**: `is_admin`, `is_affiliate`, plus soft
  delete for customer deactivation.
- **Idempotency on payment confirmation** via `lockForUpdate()` +
  gateway-reference uniqueness on `Order` / `InstallmentPayment`.
- **Row locks** for balance/stock-sensitive ops (affiliate balances, plan
  `amount_paid`, stock decrement).
- **Nigerian phone regex**: `/^(\+?234|0)[789]\d{9}$/`
- **Currency NGN, prefix `₦`**, `decimal:2` casts — avoid brick/math math
  on raw model values, pass `(string) $value` when needed.
- **EdgeCache middleware** for public catalog pages (admin bypasses) — see
  `edge-cache` aliased middleware on routes in `routes/web.php`.
- **All Mailables `implements ShouldQueue`** — production needs a queue
  worker (or `QUEUE_CONNECTION=sync` for simple setups).
- **Bank account numbers encrypted at rest** via `'encrypted'` cast on
  `User` + `Affiliate`.
- **HTTPS forced** via `URL::forceScheme('https')` in production +
  `.htaccess` redirect.

## Subsystem map

### Cart + Checkout (Phase 9)

- Session cart; polymorphic items are `ProductVariant` / `Product` /
  `Animal` — **never `Land`** (lands don't go through the cart).
- Paystack + Flutterwave hosted checkout; webhook signature validated.
- `OrderService` exposes: `placeOrder`, `markPaid` (idempotent),
  `markStatus`, `cancel`, `markRefunded`.
- Stock decrement on payment, restock on cancel/refund.

Key files: `app/Services/{CartService,OrderService}.php`,
`app/Http/Controllers/{CartController,CheckoutController,WebhookController}.php`.

### Affiliates (Phase 14)

- Self-service signup at `/affiliate/signup`; auto-approve toggleable via
  the `affiliate_auto_approve_signup` setting.
- Unique code `BLM-XXXXXX` generated at signup.
- Cart code apply re-prices items with `affiliate_price`; self-referral
  blocked at order placement.
- Commissions lifecycle: **pending → available** (after delivery + hold
  days) **→ withdrawn** (or **reversed**).
- Daily `affiliate:release-pending-commissions` at 06:00.
- Withdrawals to Nigerian bank; admin marks paid with bank reference.
- Filament resources: Affiliates, Commissions, Withdrawals + 2 dashboard
  widgets.
- **8 Mailables** under `app/Mail/Affiliate*` + `AdminNewAffiliateSignup`.

Key files: `app/Services/AffiliateService.php`, `app/Filament/Resources/Affiliate*`.

### Installments (Phase 15)

- For **lands + gadgets only** (not collections, not animals).
- `InstallmentPlan` is polymorphic (`installable` = `Land` / `Product` /
  `ProductVariant`).
- **Status machine**:
  `pending_approval` (lands) / `awaiting_down_payment` → `active` →
  `completed` → `awaiting_fulfillment` → `fulfilled`; or `defaulted` →
  `awaiting_refund` → `refunded`.
- **10% forfeiture** on default / customer cancellation (configurable).
- Land plan activation locks the plot in `reserved_installment` status.
- Gadget plan completion auto-decrements stock if available; otherwise
  stays at `completed` for wait-or-refund customer choice.
- **4 daily commands** (see `routes/console.php`):
  - `installments:detect-defaults` (00:30)
  - `installments:check-land-defaults` — 90-day resale wait (00:45)
  - `installments:send-payment-reminders` (08:00)
  - `installments:send-deadline-warnings` (08:15)
- **17 Mailables**, PDF receipts + statements via dompdf.
- Affiliate code at plan start **locks in** `affiliate_price` + commission;
  commission only released when plan completes.

Key files: `app/Services/InstallmentService.php`, `app/Console/Commands/*Installment*`,
`app/Filament/Resources/Installment*`.

### Inspections (Phase 3)

- Lands + animals support `/inspect` booking with a date/time slot.
- Admin approves with meeting location → triggers customer email.

Key files: `app/Http/Controllers/InspectionController.php`,
`app/Models/Inspection.php`.

### Inquiries (Phase 7)

- Public contact form → admin notification with rate limiting.

Key files: `app/Http/Controllers/InquiryController.php`,
`app/Mail/InquiryReceivedAdmin.php`.

## Scheduled jobs (one-line view)

From `routes/console.php`:

- `prune-stale-carts` — daily, deletes carts untouched > 30 days
- `blomfree:low-stock-digest` — 07:00
- `affiliate:release-pending-commissions` — 06:00
- `installments:detect-defaults` — 00:30
- `installments:check-land-defaults` — 00:45
- `installments:send-payment-reminders` — 08:00
- `installments:send-deadline-warnings` — 08:15

All use `->onOneServer()` so the schedule is safe under horizontal scale.

## File-layout highlights

```
app/
  Console/Commands/        — 7 scheduled / management commands
  Filament/Resources/      — 16 Filament resources (admin panel)
  Http/Controllers/        — storefront + checkout + webhooks
  Mail/                    — ~36 Mailables (all queued)
  Models/                  — 23 Eloquent models
  Services/                — Cart, Order, Affiliate, Installment services
resources/js/              — Vue 3 + TypeScript Inertia frontend
routes/                    — web.php (public), auth.php (Breeze), console.php
```

## Picking it up in a new session

If you're an AI assistant resuming this project cold, start by:

1. Reading this file end-to-end.
2. `git status && git log --oneline -10` to see current state.
3. Skim `app/Services/*.php` — they encode the business rules.
4. Skim `routes/web.php` to understand the public surface.
5. Then dive into whatever subsystem the user is asking about.
