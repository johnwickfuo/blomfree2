# BLOMFREE Deployment Guide (HestiaCP, Git-Based)

This is the operator runbook for getting BLOMFREE onto John's Contabo
HestiaCP box, keeping it healthy, and pushing updates.

## TL;DR after the first deploy

```bash
# Local: build assets, commit, push.
npm run build
git add public/build
git commit -m "build: production assets" --allow-empty
git push origin main

# Server: one-liner pull-and-deploy.
ssh <hestia-user>@<server-ip> "cd /home/<hestia-user>/web/blomfree.<domain>.com/app && ./deploy.sh"
```

That's it. The rest of this document covers the one-time setup and
every operational concern (queues, cron, webhooks, backups, domain
migration, smoke tests).

## What's in this guide

1. Pre-deployment local checks
2. HestiaCP subdomain setup
3. Server-side first-time deploy
4. Queue worker (Supervisor) — **critical**
5. Cron (scheduled commands) — **critical**
6. SSL + HTTPS
7. Webhook configuration
8. Security hardening
9. Smoke tests against test keys
10. Monitoring + backups
11. `deploy.sh` for future updates
12. Domain migration procedure
13. Final verification print-out

---

## 1. Pre-deployment local checks

Run from the project root before pushing to main:

```bash
php artisan test                         # 23 tests, all green
npm run build                            # builds public/build assets
rm -f database/database.sqlite && touch database/database.sqlite \
    && php artisan migrate:fresh --seed  # confirms migrations + seeders are clean
grep -rE '(sk_live_|FLWSECK_[A-Z])' app/ config/ routes/ --include='*.php'   # must return nothing
git ls-files | grep -E '^\.env$'         # must return nothing — .env is never committed
git ls-files public/build/ | head -1     # must show built JS — Option 1 (commit build)
```

Smoke-test locally: register, affiliate signup, admin login,
checkout-with-test-card, start installment plan, contact form.

## 2. HestiaCP subdomain setup

In the Contabo HestiaCP UI:

1. Create a subdomain, e.g. `blomfree.<existing-domain>.com`. Set PHP
   to **8.2+**.
2. Create a MySQL database. Note the credentials (you'll paste them
   into `.env` in step 3).
3. **Do NOT enable SSL yet.** Let's Encrypt validation requires the
   site to respond on HTTP first.

## 3. Server-side first-time deploy

SSH in:

```bash
ssh <hestia-user>@<server-ip>
cd /home/<hestia-user>/web/blomfree.<domain>.com
```

### 3a. SSH deploy key for GitHub (recommended)

```bash
ssh-keygen -t ed25519 -C "hestia-deploy@blomfree" -f ~/.ssh/blomfree_deploy -N ""
cat ~/.ssh/blomfree_deploy.pub
```

Add the public key as a **read-only deploy key** at
`https://github.com/<your-username>/blomfree/settings/keys`.

Configure SSH to use it:

```bash
cat >> ~/.ssh/config <<'EOF'
Host github.com-blomfree
    HostName github.com
    User git
    IdentityFile ~/.ssh/blomfree_deploy
    IdentitiesOnly yes
EOF
chmod 600 ~/.ssh/config
ssh -T github.com-blomfree     # should print "Hi <user>/blomfree! You've successfully authenticated..."
```

### 3b. Clone — Option A (recommended): app outside web root, symlink in

```bash
mv public_html public_html.bak
git clone git@github.com-blomfree:<your-username>/blomfree.git app
ln -s /home/<hestia-user>/web/blomfree.<domain>.com/app/public public_html
```

Final layout:

```
/home/<hestia-user>/web/blomfree.<domain>.com/
├── app/                <-- Laravel project root (the git clone)
├── public_html -> app/public
└── public_shtml/
```

If HestiaCP refuses the symlink, fall back to Option B (clone directly
into `public_html`, then change the subdomain's web root to
`public_html/public` in HestiaCP).

### 3c. Composer + .env + key + storage

```bash
cd /home/<hestia-user>/web/blomfree.<domain>.com/app
composer install --no-dev --optimize-autoloader --no-interaction
cp .env.example .env
nano .env       # fill in production values — see below
php artisan key:generate --force
chmod 640 .env
chown <hestia-user>:www-data .env
```

Production `.env` values to set (everything else can stay at defaults):

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://blomfree.<domain>.com
APP_TIMEZONE=Africa/Lagos
LOG_CHANNEL=daily
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=<hestia-db-name>
DB_USERNAME=<hestia-db-user>
DB_PASSWORD=<hestia-db-password>

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=public

MAIL_MAILER=smtp
MAIL_HOST=<your-smtp-host>
MAIL_PORT=587
MAIL_USERNAME=<smtp-user>
MAIL_PASSWORD=<smtp-pass>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@blomfree.com"

ADMIN_NOTIFICATION_EMAIL=admin@blomfree.com

# IMPORTANT: start with TEST keys until business verification completes.
PAYSTACK_PUBLIC_KEY=pk_test_xxx
PAYSTACK_SECRET_KEY=sk_test_xxx
FLUTTERWAVE_PUBLIC_KEY=FLWPUBK_TEST-xxx
FLUTTERWAVE_SECRET_KEY=FLWSECK_TEST-xxx
FLUTTERWAVE_SECRET_HASH=<your-secret-hash>
```

### 3d. Migrate + production seeders + storage link + caches

```bash
php artisan migrate --force
php artisan db:seed --class=ShippingZoneSeeder --force
php artisan db:seed --class=SettingSeeder --force
# DO NOT run AffiliateSeeder, InstallmentSeeder, ProductSeeder, AnimalSeeder, or LandSeeder
# — those contain placeholder demo content.

php artisan storage:link
chmod -R 775 storage bootstrap/cache
chown -R <hestia-user>:www-data storage bootstrap/cache

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

php artisan blomfree:create-admin   # use Saturday's real email
```

## 4. Queue worker (Supervisor) — CRITICAL

All emails (order confirmations, affiliate notifications, installment
notifications, refund alerts) now route through the database queue.
Without a running worker, **no emails are sent at all**.

Create `/etc/supervisor/conf.d/blomfree-worker.conf`:

```
[program:blomfree-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/<hestia-user>/web/blomfree.<domain>.com/app/artisan queue:work --sleep=3 --tries=3 --max-time=3600 --memory=512
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=<hestia-user>
numprocs=1
redirect_stderr=true
stdout_logfile=/home/<hestia-user>/web/blomfree.<domain>.com/app/storage/logs/worker.log
stopwaitsecs=3600
```

Activate:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start blomfree-worker:*
sudo supervisorctl status blomfree-worker:*   # must show RUNNING
```

If HestiaCP doesn't expose Supervisor root access, ask the server
admin to install + configure it. Fallback (inferior — a cron-based
watchdog):

```
*/5 * * * * pgrep -f "queue:work" > /dev/null || nohup php /home/<hestia-user>/web/blomfree.<domain>.com/app/artisan queue:work --max-time=3600 >> /home/<hestia-user>/web/blomfree.<domain>.com/app/storage/logs/worker.log 2>&1 &
```

## 5. Cron (scheduled commands) — CRITICAL

Several commands run daily. Without cron, **installments never default,
affiliate commissions never become withdrawable, customers never get
payment reminders.**

Via HestiaCP cron manager or `crontab -e -u <hestia-user>`:

```
* * * * * cd /home/<hestia-user>/web/blomfree.<domain>.com/app && php artisan schedule:run >> /dev/null 2>&1
```

That single line is enough — `routes/console.php` already registers
the actual cadence for each command:

| Command | Time | What it does |
| --- | --- | --- |
| `prune-stale-carts` (closure) | 00:00 | Deletes session carts not touched in 30 days |
| `installments:detect-defaults` | 00:30 | Flips overdue active plans to defaulted |
| `installments:check-land-defaults` | 00:45 | Auto-refunds resold lands; alerts admin on the rest |
| `affiliate:release-pending-commissions` | 06:00 | Promotes pending commissions whose hold elapsed |
| `blomfree:low-stock-digest` | 07:00 | Nightly low-stock report to admin |
| `installments:send-payment-reminders` | 08:00 | 3-day-ahead reminders for suggested due dates |
| `installments:send-deadline-warnings` | 08:15 | 30/14/7/3/1-day warnings to customer (+ admin at 7) |

Verify: `php artisan schedule:list`. Smoke test: `php artisan schedule:run`.

## 6. SSL + HTTPS

In HestiaCP, enable Let's Encrypt for the subdomain. **The site must
respond on HTTP first** for validation to succeed. After SSL is
active:

- `public/.htaccess` already enforces HTTPS in production (skipped
  for localhost so `php artisan serve` keeps working locally).
- `AppServiceProvider::boot()` calls `URL::forceScheme('https')` when
  `APP_ENV=production`.
- HSTS, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, and
  Permissions-Policy headers are set in `public/.htaccess`.

Once SSL is up, set `SESSION_SECURE_COOKIE=true` in `.env`, then
`php artisan config:cache`.

## 7. Webhook configuration

### Paystack

1. `https://dashboard.paystack.com` → Settings → API Keys & Webhooks
2. Add webhook URL: `https://blomfree.<domain>.com/webhooks/paystack`

### Flutterwave

1. `https://dashboard.flutterwave.com` → Settings → Webhooks
2. Add webhook URL: `https://blomfree.<domain>.com/webhooks/flutterwave`
3. Set the secret hash; copy it into `.env` as `FLUTTERWAVE_SECRET_HASH`
4. `php artisan config:cache`

Both handlers verify the gateway signature server-side and route by
metadata (`order_reference` → Order, `payment_type=installment` →
InstallmentPlan).

Test from each dashboard's webhook UI and `tail -f storage/logs/laravel.log`.

## 8. Security hardening

Already in code:

- **Bank account numbers** encrypted at rest via Eloquent `'encrypted'`
  cast on both `User` and `Affiliate` models. Bank names and account
  holder names stay in clear text so admin can verify transfers.
- **CSRF** on every state-changing route except `/webhooks/*` (signed
  by gateway).
- **Rate limits**: `/register` 5/h, `/login` 5/min (Breeze), `/affiliate/signup`
  3/day, `/contact` 5/h, `/inspections` 5/h, `/cart/add` 30/min,
  `/installments/initiate` 10/h, `/installments/{plan}/initiate-payment` 30/h,
  `/affiliate/dashboard/withdrawals` 5/h.
- **Security headers** in `public/.htaccess`: HSTS, X-Frame, no-sniff,
  Referrer-Policy, Permissions-Policy. `X-Powered-By` stripped.

File permissions audit:

```bash
find . -type f -name '*.php' -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod 600 .env
chmod -R 775 storage bootstrap/cache
```

## 9. Smoke tests (with TEST keys)

**Public:** homepage, /about, /contact, /lands, /kennel-farm,
/collections, /gadgets, /installments, /affiliate.

**Customer flow:** register → email confirmation → add gadget →
checkout with Paystack test card `4084 0840 8408 4081` → order
appears in admin → confirmation email arrives → stock decremented.
Repeat with Flutterwave test card.

**Affiliate flow:** signup → welcome email + admin notification →
place order with affiliate code from a separate browser →
`AffiliateCommission` created → backdate `available_at`, run
`php artisan affiliate:release-pending-commissions` → balance
released → set bank details → request withdrawal → admin marks
paid → affiliate notified.

**Installment flow (gadget):** start plan → pay down payment →
activated → pay remainder → `awaiting_fulfillment` → admin marks
fulfilled. **Installment flow (land):** start plan → `pending_approval`
→ admin approves with custom schedule → customer pays down payment →
`reserved_installment`. **Default flow:** backdate a plan's deadline,
run `php artisan installments:detect-defaults` → defaults + refund
record created.

**Inspections:** submit on a land → admin notified → approve with
meeting details → customer email with address.

**Infra:** send test webhooks from each gateway dashboard, confirm
in `storage/logs/laravel.log`. `php artisan schedule:list` shows all
commands. `sudo supervisorctl status blomfree-worker:*` says
`RUNNING`. `tail -f storage/logs/worker.log` shows jobs flowing.

If any test fails, do not switch to live keys.

## 10. Monitoring + backups

**Daily DB backup** (HestiaCP built-in, or):

```
0 3 * * * mysqldump -u<db-user> -p<db-password> <db-name> | gzip > /home/<hestia-user>/backups/blomfree-$(date +\%Y\%m\%d).sql.gz
0 4 * * 0 find /home/<hestia-user>/backups -name 'blomfree-*.sql.gz' -mtime +30 -delete
```

**Error monitoring**: email exceptions to admin@blomfree.com via
Laravel's exception handler, or wire up Sentry (free tier covers up
to 5k errors/month).

**Uptime**: UptimeRobot or BetterUptime free tier, 5-minute checks
against the public URL.

## 11. deploy.sh for future updates

`deploy.sh` (committed to the repo) pulls, composer-installs, runs
migrations, rebuilds caches, restarts the queue worker, fixes perms.
Run from the project root on the server.

Future deploys:

```bash
# Local: rebuild + push.
npm run build
git add public/build
git commit -m "build: production assets" --allow-empty
git push origin main

# Server: one-liner.
ssh <hestia-user>@<server-ip> "cd /home/<hestia-user>/web/blomfree.<domain>.com/app && ./deploy.sh"
```

## 12. Domain migration procedure

When the real domain replaces the subdomain:

1. Add the real domain in HestiaCP under the same user.
2. Point DNS A record at the server IP.
3. Enable Let's Encrypt for the real domain.
4. Symlink the new domain's `public_html` to the same `app/public`.
5. `.env`: update `APP_URL=https://<real-domain>` → `php artisan config:cache`.
6. **Update webhook URLs in BOTH Paystack and Flutterwave dashboards.**
   This is the #1 silent failure when migrating domains.
7. Send a test transaction on the new domain.
8. Add a 301 redirect from the old subdomain to the new domain.
9. Update social media + external references.

## 13. Final verification print-out

After completing all steps:

```bash
echo "=== BLOMFREE DEPLOYMENT VERIFICATION ===" && \
echo "Git branch: $(git rev-parse --abbrev-ref HEAD)" && \
echo "Git commit: $(git rev-parse --short HEAD)" && \
echo "APP_URL: $(grep '^APP_URL=' .env)" && \
echo "APP_ENV: $(grep '^APP_ENV=' .env)" && \
echo "DB: $(php artisan tinker --execute='echo DB::connection()->getDatabaseName();')" && \
echo "Migrations status:" && php artisan migrate:status | tail -5 && \
echo "Scheduled tasks:" && php artisan schedule:list && \
echo "Queue worker:" && sudo supervisorctl status blomfree-worker:* && \
echo "Storage link:" && ls -la public/storage && \
echo "Settings count:" && php artisan tinker --execute='echo App\Models\Setting::count();' && \
echo "Admin users count:" && php artisan tinker --execute='echo App\Models\User::where("is_admin", true)->count();'
```

When you're satisfied with the smoke tests, work through
`GO_LIVE_CHECKLIST.md` to flip from TEST to LIVE gateway keys.
