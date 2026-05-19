# BLOMFREE Affiliate Program — Admin Guide

This guide covers everything admins need to operate the affiliate program: how
it works, what to do on the dashboard, and the runbooks for the common ops
tasks (approving withdrawals, suspending bad actors, releasing pending
commissions, etc.).

## How the program works at a glance

- An affiliate signs up at `/affiliate/signup`. A `User` (with
  `is_affiliate = true`) and an `Affiliate` row are created in one
  transaction. They get a unique code like `BLM-AB12CD`.
- The affiliate shares their code. Buyers enter it at checkout via the
  Cart page — there are no referral links or tracking cookies.
- When the code is applied, every cart line is re-priced: items configured
  with an `affiliate_price` swap their displayed price; items without it
  stay at the normal price.
- When the order is paid, one `AffiliateCommission` row per eligible item
  is created at the fixed `affiliate_unit_commission * quantity` rate.
  The affiliate's `pending_balance` and `total_earned` increase.
- The commission becomes `available` once the order is delivered (plus a
  short hold period) or after the configurable fallback hold from
  payment time.
- The affiliate requests a withdrawal from the `available_balance`. An
  admin marks it paid after sending the bank transfer.
- If the order is later cancelled or refunded, related commissions are
  reversed and balances decremented.

## What's eligible

- **Collections** products
- **Gadgets** products
- **Kennel & Farm** animals (both pool and individual)

**Lands are not eligible** — they're high-value, slow-converting, and
governed by separate documents.

A product or animal becomes eligible the moment an admin sets both an
`affiliate_price` (≤ the regular price) and an `affiliate_commission > 0`.
The system derives `affiliate_enabled` automatically — there's no toggle
to forget.

## Admin navigation

All affiliate management lives under the **Affiliates** group in the
sidebar:

| Resource | What it's for |
| --- | --- |
| Affiliates | Browse all affiliates, suspend / reactivate, apply manual credit / debit. |
| Commissions | Audit every commission ever earned. Reverse one manually if you have to. |
| Withdrawals | Approve & pay out withdrawal requests, or reject and refund. |

The dashboard at `/admin` shows the **AffiliateOverviewWidget** (4 KPIs)
and the **TopAffiliatesWidget** (the 5 highest lifetime earners).

## Common admin tasks

### Approving a withdrawal

1. Open **Affiliates → Withdrawals**. The default filter shows pending
   requests; the sidebar badge counts them too.
2. Open the row — the bank details snapshot is shown (this is the
   account the affiliate had on file when they submitted; later changes
   to their profile don't affect what you should pay).
3. Send the bank transfer from your business account.
4. Back in the admin, click **Mark Paid**, paste the bank transfer
   reference, optionally add admin notes, and confirm.

The system marks the withdrawal `paid`, links the oldest available
commissions (FIFO) to the withdrawal until the amount is covered, and
emails the affiliate a confirmation with the payment reference.

### Rejecting a withdrawal

If you can't process a withdrawal (wrong bank details, suspicious
activity, etc.):

1. Open the withdrawal.
2. Click **Reject**, give a reason (this is shared with the affiliate).
3. The amount is returned to the affiliate's `available_balance` and
   `total_withdrawn` is rolled back.

### Suspending an affiliate

1. Open **Affiliates → Affiliates**.
2. Click **Suspend**, enter the reason. The reason is included in the
   email and visible on their `/affiliate/suspended` page.

Suspending means: their code is silently rejected at checkout, they
can't request new withdrawals, but their existing balances and
commission history are preserved. Use **Reactivate** to lift it.

### Manual balance adjustment

If you owe (or owed-back) an affiliate outside the normal commission
flow — e.g. you ran a manual promotion, or you need to claw back a
mistake — use the **Manual credit / debit** action.

Every adjustment writes an `AffiliateBalanceAdjustment` audit row
recording the admin user, amount, and reason. The affiliate's
`available_balance` changes immediately; for credits, `total_earned`
also rises (so their lifetime number stays honest).

### Manually reversing a commission

From **Affiliates → Commissions**, the **Reverse** action lets you
zero out a single commission with a reason. This is mostly a safety
valve for edge cases — the normal cancel/refund flow on an order
handles reversal automatically.

## Configuration

Settings live in the `settings` table, editable in **Settings →
Settings**:

| Key | Default | Meaning |
| --- | --- | --- |
| `affiliate_auto_approve_signup` | `1` | If `0`, new signups land in `suspended` and require admin approval (set status to `active`). |
| `affiliate_admin_notification_email` | `admin@blomfree.com` | Where signup, withdrawal request, and balance-warning emails go. Falls back to `admin_notification_email`. |
| `affiliate_commission_hold_days` | `7` | Days after delivery before a pending commission unlocks. |
| `affiliate_commission_hold_fallback_days` | `30` | If we never confirm delivery, the commission auto-unlocks this many days after payment. |
| `affiliate_minimum_withdrawal` | `5000` | Minimum NGN per withdrawal request. |
| `affiliate_withdrawal_fee` | `100` | Fixed transfer fee deducted from each payout. |

## Scheduled tasks

A cron-driven release runs daily at 06:00 server time:

```
php artisan affiliate:release-pending-commissions
```

It promotes any `pending` commission whose `available_at` has passed
to `available`, moves the money between `pending_balance` and
`available_balance`, and is safe to re-run.

It's already wired in `routes/console.php` — just make sure
`php artisan schedule:run` is in your cron.

## Edge cases worth knowing

- **Self-referral**: blocked at order placement. If an affiliate
  enters their own code on their own checkout, the code is silently
  dropped and the order is recorded as un-referred.
- **Negative balances after reversal**: if a refund hits an affiliate
  who's already withdrawn the money, the available balance can go
  negative. The system logs a warning and emails the admin
  (`emails.affiliate-commission-reversed-admin`) — review and apply a
  manual debit or talk to the affiliate.
- **Suspended code mid-checkout**: the code is re-validated at order
  placement. If the affiliate gets suspended between cart-apply and
  submit, the order is recorded as un-referred.
- **Price changes after add-to-cart**: applying or removing the
  affiliate code in the cart calls `CartService::repriceAll()` so
  every line is re-evaluated against current product pricing.

## Operational checklist

- [ ] Set `affiliate_admin_notification_email` to a monitored inbox.
- [ ] Make sure the scheduler cron runs daily.
- [ ] Decide on auto-approve or manual: flip `affiliate_auto_approve_signup`.
- [ ] Sanity-check the minimum withdrawal and fee for your bank reality.
- [ ] Tell your buyers about the program — there's a public landing at `/affiliate`.
