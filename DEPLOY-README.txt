================================================================
BLOMFREE & CO. NIG. LTD - DEPLOYMENT GUIDE
================================================================

You have two files:
  blomfree-deploy.zip      <- this archive (the project)
  blomfree-database.sql    <- the database to import

================================================================
STEP 1: CREATE DATABASE IN HESTIACP
================================================================

1. Log into HestiaCP
2. Databases > Add Database
3. Name it (HestiaCP usually prefixes it like admin_blomfree)
4. Create a database user with a strong password
5. WRITE DOWN the database name, username, and password

================================================================
STEP 2: IMPORT THE DATABASE
================================================================

Option A (via phpMyAdmin in HestiaCP):
  1. Databases > click the database you just created > phpMyAdmin
  2. Click "Import" tab at the top
  3. Choose blomfree-database.sql
  4. Click "Go" / "Import"
  5. Wait for "Import has been successfully finished"

Option B (via SSH):
  mysql -u <db-user> -p <db-name> < blomfree-database.sql

================================================================
STEP 3: UPLOAD AND EXTRACT THE ZIP
================================================================

1. Log into HestiaCP > File Manager
2. Navigate to: /home/<your-user>/web/<your-domain>/public_html
3. Upload blomfree-deploy.zip
4. Extract it in place (right-click > Extract)
5. Delete blomfree-deploy.zip after extraction

After extraction, public_html/ should contain folders like:
  app/  bootstrap/  config/  database/  public/  resources/  routes/
  storage/  vendor/  artisan  composer.json  .env.example  etc.

================================================================
STEP 4: SET WEB ROOT TO public_html/public
================================================================

Laravel serves from a subfolder called "public", not the project root.
Configure this in HestiaCP:

1. Web > click your domain > Edit
2. Find "Document Root" or "Web Template" settings
3. Change document root to:
   /home/<your-user>/web/<your-domain>/public_html/public
4. Save

If HestiaCP does not allow setting a custom document root, you can
instead add a .htaccess file at public_html/ that rewrites all
requests into public/. Ask if you need this option.

================================================================
STEP 5: CONFIGURE .env
================================================================

Via SSH OR HestiaCP File Manager:

1. Copy .env.example to .env (in the public_html folder, not public/)
2. Edit .env and fill in at minimum:

   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://<your-domain>
   APP_TIMEZONE=Africa/Lagos

   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=<from-step-1>
   DB_USERNAME=<from-step-1>
   DB_PASSWORD=<from-step-1>

   ADMIN_NOTIFICATION_EMAIL=<your-email>

   MAIL_MAILER=smtp
   MAIL_HOST=<your-smtp-host>     # leave blank if no SMTP yet
   MAIL_PORT=587
   MAIL_USERNAME=<smtp-user>
   MAIL_PASSWORD=<smtp-pass>
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=noreply@<your-domain>

3. Save the file.

================================================================
STEP 6: GENERATE APP KEY AND FIX PERMISSIONS
================================================================

Via SSH:

   cd /home/<your-user>/web/<your-domain>/public_html
   php artisan key:generate --force
   chmod -R 775 storage bootstrap/cache
   chown -R <your-user>:<your-user> .

================================================================
STEP 7: CREATE ADMIN USER
================================================================

   php artisan blomfree:create-admin

Enter name, email, and password when prompted. You will use this
to log into /admin (the Filament dashboard).

================================================================
STEP 8: CACHE FOR PERFORMANCE
================================================================

   php artisan config:cache
   php artisan route:cache
   php artisan view:cache

================================================================
STEP 9: TEST IT
================================================================

Visit: https://<your-domain>

You should see the BLOMFREE homepage.

Admin login: https://<your-domain>/admin

================================================================
OPTIONAL BUT RECOMMENDED: QUEUE WORKER AND CRON
================================================================

The site will work without these, but emails will not send and
affiliate commissions / installments will not auto-process.

To skip queues temporarily, set in .env:
   QUEUE_CONNECTION=sync
This makes emails send synchronously (slower checkout but works).

For full functionality, set up later:

1. Queue worker (Supervisor):
   See full deployment guide for /etc/supervisor/conf.d setup.

2. Cron (Laravel scheduler):
   In HestiaCP > Cron > Add:
     * * * * * cd /home/<your-user>/web/<your-domain>/public_html && php artisan schedule:run >> /dev/null 2>&1

================================================================
TROUBLESHOOTING
================================================================

Blank page or 500 error:
  Check: tail -50 storage/logs/laravel.log
  Common: .env values wrong, or storage/ not writable

CSS/JS not loading:
  Check: public/build/ folder exists with hashed files
  Check: APP_URL in .env matches the actual domain (no trailing slash)

Login works but /admin redirects to home:
  Check: that user has is_admin = 1 in the database
  Fix:  mysql > UPDATE users SET is_admin=1 WHERE email='your@email';

Routes return 404:
  Check: web root is set to public_html/public, not public_html

================================================================
