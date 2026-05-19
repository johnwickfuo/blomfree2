# BLOMFREE Go-Live Checklist

Everything that must be true before swapping from test to live
gateway keys, plus the swap procedure and post-launch monitoring.

## Pre-launch — business

- [ ] Paystack business verification complete, live keys issued
- [ ] Flutterwave business verification complete, live keys issued
- [ ] CEO Saturday has logged in to `/admin` and reset her password
- [ ] Real CEO photo + bio published on `/about`
- [ ] Real installment program terms text finalized (currently v1.0
      placeholder; bump `installment_terms_version` setting if edited)
- [ ] Real affiliate program terms text finalized on `/affiliate/terms`
- [ ] Logo (real, not placeholder) uploaded
- [ ] OG image (1200×630 PNG) uploaded at `public/og-default.jpg`

## Pre-launch — content

- [ ] At least 3 real lands published (with photos and full document status)
- [ ] At least 5 real animals published (with photos and pricing)
- [ ] At least 5 real Collections products published
- [ ] At least 5 real Gadgets products published
- [ ] Shipping zones reviewed and correct (`ShippingZoneSeeder`
      is a starting point; admin can edit in Filament)
- [ ] All test users, test orders, test affiliates, test installment
      plans deleted from the production DB

## Pre-launch — technical

- [ ] All smoke tests in `DEPLOYMENT.md` Section 9 pass with TEST keys
- [ ] `php artisan schedule:list` shows all 7 daily jobs
- [ ] `sudo supervisorctl status blomfree-worker:*` shows RUNNING
- [ ] Daily DB backup ran successfully for 7+ consecutive days
- [ ] At least one backup recovery test performed (restore the SQL
      dump into a throwaway DB and confirm tables match)
- [ ] HTTPS redirect confirmed working — `http://blomfree.<domain>` 301s to https
- [ ] HSTS header confirmed in browser dev tools
- [ ] Webhook URLs reachable from gateway dashboards (test webhook
      from each dashboard, confirm in `storage/logs/laravel.log`)
- [ ] Uptime monitor enabled (UptimeRobot / BetterUptime)
- [ ] Admin notification inbox is monitored (`admin_notification_email`,
      `affiliate_admin_notification_email`, `installment_admin_notification_email`)

## Switching to live keys

```bash
# On the server, in the project root:
nano .env
#   PAYSTACK_PUBLIC_KEY=pk_live_xxx
#   PAYSTACK_SECRET_KEY=sk_live_xxx
#   FLUTTERWAVE_PUBLIC_KEY=FLWPUBK-xxx-X
#   FLUTTERWAVE_SECRET_KEY=FLWSECK-xxx-X
#   FLUTTERWAVE_SECRET_HASH=<live secret hash from dashboard>

php artisan config:cache
```

- [ ] Verify webhook URLs in both Paystack and Flutterwave dashboards
      still point to the live URL
- [ ] Run one ₦100 real transaction end to end (real card, real
      money). Confirm the order appears, the customer email arrives,
      the affiliate commission accrues if a code was used. Refund it
      manually from the gateway dashboard afterwards.
- [ ] Announce go-live to the team

## Post-launch (first 72 hours)

- [ ] Check `storage/logs/laravel.log` every 4 hours for errors
- [ ] Check `storage/logs/worker.log` to confirm queue jobs are
      processing (no stalled jobs)
- [ ] Verify daily scheduled commands actually ran each morning
      (check log timestamps)
- [ ] Verify a real customer can complete: register → checkout →
      receive emails
- [ ] Respond to support inquiries within 24 hours
- [ ] If anything looks off, switch back to test keys via the same
      `.env` flow + `php artisan config:cache` and investigate
