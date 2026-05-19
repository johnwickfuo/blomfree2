# BLOMFREE Installment Program — Admin Guide

This guide covers operating the installment system: how plans flow,
the admin actions you'll do most days, and the scheduled commands
that keep the system in shape.

## How the program works at a glance

- A customer creates an account at `/register` (registration is open for
  customers in Phase 15).
- On an eligible item page (a land or a gadget with `installment_enabled`),
  they click **Pay in Installments**.
- They land on a preview page that shows total, down payment %, max length,
  computed deadline, and the 10% forfeiture clause. They tick the terms
  checkbox and submit.
- **Lands** → plan is `pending_approval`. Admin gets an email and reviews
  it in `Installments → Plans`.
- **Gadgets** → plan jumps straight to `awaiting_down_payment`.
- Customer pays a down payment (≥ minimum %) via Paystack or Flutterwave.
  Plan flips to `active`, deadline is set as activation date + max months.
  For lands, the plot is marked `reserved_installment`.
- Customer pays any amount any time until the deadline. Once the total
  is paid:
  - **Gadgets** with stock → plan auto-progresses to
    `awaiting_fulfillment`, stock is decremented, admin ships.
  - **Gadgets** without stock → plan stays `completed` and customer
    sees a wait-or-refund choice (only the wait path is implemented in
    the UI today; admin can issue the no-forfeiture refund via the
    plan's **Trigger refund** action with `item_unavailable_at_completion`).
  - **Lands** → plan moves to `awaiting_fulfillment`, admin handles
    the document transfer, then marks fulfilled (land becomes `sold`).
- If the deadline passes without completion, the daily
  `installments:detect-defaults` command flips the plan to `defaulted`
  and (for gadgets) auto-creates a refund record.

## What's eligible

- **Lands** (Estates & Properties)
- **Gadgets** (gadgets & accessories) — both standalone and variant
  products
- **Not eligible**: Collections (clothing) and Kennel & Farm (animals)

A product or land becomes eligible the moment an admin sets
`installment_enabled = true` plus both `installment_minimum_down_payment_percentage`
and `installment_maximum_length_months`. Variants inherit from their
parent product if their own values are null.

## Admin navigation

The **Installments** sidebar group has three resources:

| Resource | What it's for |
| --- | --- |
| Plans | All plans, with approve/reject (for lands), record offline payment, mark fulfilled, trigger refund, force default. |
| Payments | Read-only audit log of every payment. |
| Refunds | Workflow inbox for refund payouts — mark paid (with bank reference) or reject. |

The dashboard `/admin` shows an **InstallmentOverviewWidget** with 5
KPIs (pending requests, deadlines ≤ 7d, defaulted this month,
pending refunds, total active value).

## Common admin tasks

### Approving a land installment request

1. Open **Installments → Plans**. The default sidebar badge counts
   `pending_approval` items.
2. Click into the plan, review the customer's contact details and any
   notes they left.
3. Click **Approve** to flip the plan to `awaiting_down_payment`. The
   customer gets an email with a link to make their down payment.
4. Click **Reject** to decline with a reason (no payments to refund yet
   at this stage).

### Recording an offline payment

Use the **Record offline payment** action on any `awaiting_down_payment`
or `active` plan when a customer transfers directly to your bank
outside the gateway flow. Stored with `payment_gateway = manual` and
counted exactly like a gateway payment for plan progression.

### Marking a plan fulfilled

When a customer has fully paid and you've shipped the gadget or
transferred the land documents, use **Mark fulfilled**. Lands
automatically transition to `sold` at this point.

### Processing a refund

1. Open **Installments → Refunds** (default filter shows `pending`).
2. Verify the bank snapshot — that's the bank info the customer had
   when the refund was created (later profile changes don't affect
   what you should pay).
3. Send the bank transfer.
4. Click **Mark Paid**, paste your bank reference, optionally add
   notes. The plan moves to `refunded` and the customer is emailed.

If the customer has no bank details on file, the refund record will
note this — wait until they fill them in before processing.

### Triggering a manual refund

From a plan that's `defaulted` or `completed` (item unavailable case),
the **Trigger refund** action lets you create an `InstallmentRefund`
record. Pick the trigger reason:

- `admin_override` — anything else, including land plans you want to
  refund before the 90-day wait period elapses
- `land_resold` — set by the daily command automatically when a
  defaulted land has been resold
- `item_unavailable_at_completion` — gadget plan completed but stock
  is gone; forfeiture is set to 0 so the customer gets 100% back

## Configuration

Editable in **Settings → Settings**:

| Key | Default | Meaning |
| --- | --- | --- |
| `installment_signup_enabled` | `1` | Master switch (currently informational; the model itself controls eligibility). |
| `installment_forfeiture_percentage` | `10` | % retained on default or customer cancellation. |
| `installment_land_resale_wait_days` | `90` | Days after land default before auto-refund check fires. |
| `installment_terms_version` | `v1.0` | Versioned terms text. Bump when material changes ship. |
| `installment_admin_notification_email` | `admin@blomfree.com` | Where pending-request, defaulted, and refund-request emails go. |
| `installment_due_reminder_days_before` | `3` | Reminder lead time before each suggested due date. |
| `installment_overdue_warning_days` | `30,14,7,3,1` | Days before deadline that customer (and admin at 7d) get warnings. |

## Scheduled commands

All wired in `routes/console.php`, running daily:

| Command | When | What |
| --- | --- | --- |
| `installments:detect-defaults` | 00:30 | Flips active plans whose deadline has passed to `defaulted` (gadgets auto-create refund; lands return to public listings). |
| `installments:check-land-defaults` | 00:45 | For land plans defaulted > 90 days, creates a refund if the plot has resold, or emails admin for manual decision. |
| `installments:send-payment-reminders` | 08:00 | Emails customers 3 days before each suggested due date (once per entry). |
| `installments:send-deadline-warnings` | 08:15 | Emails customers at 30/14/7/3/1 days before deadline; admin gets a copy at 7d. |

Make sure your cron runs `php artisan schedule:run` every minute and
your `APP_URL` is set — Mailables that link to the customer dashboard
generate absolute URLs from it.

## Edge cases worth knowing

- **Self-overlap on lands**: only one active plan can exist per plot.
  The frontend hides the Pay in Installments CTA while a plot is
  `reserved_installment`, and the server-side `store()` action also
  blocks duplicates.
- **Overpayment**: the service caps any single payment at the
  remaining balance — customers cannot accidentally overpay.
- **Concurrent payments**: every payment locks the plan row with
  `lockForUpdate()` and is keyed by `payment_reference` so double
  webhooks or rapid double-clicks can't double-credit.
- **Affiliate code at plan start**: locks in `affiliate_price` and the
  `affiliate_commission_locked` amount. If the plan completes, the
  affiliate's `pending_balance` and `total_earned` increase by that
  amount. Defaulted or cancelled plans never award commission.
- **Affiliate suspended after plan starts**: the link stays. Suspension
  blocks new uses of the code, not the in-flight plan.
- **Customer account deactivation**: blocked if any active installment
  plan exists. Customers must complete, cancel, or wait for default
  first. Deactivation is soft delete — the row stays with
  `deleted_at` set.
- **Multiple plans per customer**: allowed, including for the same
  item type.
- **Plan deadline on weekend/holiday**: no business-day adjustment —
  payments are online and can be made any time.
- **Bank-details-changed-after-refund-create**: the refund's bank
  snapshot is locked at creation time. Updating profile bank details
  later doesn't affect a pending refund. If wrong details are
  captured, admin can edit the refund (or reject and re-trigger after
  the customer fixes their profile).

## Operational checklist before launch

- [ ] Set `installment_admin_notification_email` to a monitored inbox.
- [ ] Make sure the scheduler cron runs daily.
- [ ] Confirm Paystack/Flutterwave credentials work end-to-end with a
      small test plan.
- [ ] Set up a couple of items as installment-eligible to give
      customers somewhere to start.
- [ ] Make sure the customer-facing program landing at `/installments`
      is in the main nav or footer.
- [ ] Test a refund payout from the admin to a real bank account so
      the workflow is familiar.
