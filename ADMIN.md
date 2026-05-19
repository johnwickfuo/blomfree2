# BLOMFREE — Admin Access

The admin panel lives at **`/admin`** (Filament). Access is gated by an
`is_admin` boolean flag on the `users` table — only users with `is_admin = true`
can reach the panel, in **every** environment (there is no "local bypass").

There are **no customer accounts** on this site. Public registration has been
removed; the storefront cart and checkout are guest-only. The only accounts that
exist are admin accounts.

## Creating a new admin

Run the interactive artisan command on the server:

```bash
php artisan blomfree:create-admin
```

It prompts for a name, email and password (entered twice), validates the
input, creates the user with `is_admin = true`, and prints a confirmation with
the login URL. This is the supported way to add a new admin — no direct
database editing required.

The initial admin account (`admin@blomfree.com`, created during setup) is
promoted to admin automatically by a migration, so a fresh `php artisan migrate`
keeps it working.

## Revoking admin access

Set the user's `is_admin` flag to `false`. Until a management UI exists, do this
via tinker:

```bash
php artisan tinker
>>> App\Models\User::where('email', 'someone@blomfree.com')->update(['is_admin' => false]);
```

The change takes effect on their next request — they will be denied at
`/admin` and redirected to the login screen.

To fully remove an account, delete the user row.

## Roles & permissions

There is **no role system yet** — every admin has full access to every part of
the panel. `is_admin` is a single on/off switch.

If finer-grained control is needed later (e.g. an Estates manager who can only
see Land and Inspection records), add [`spatie/laravel-permission`](https://spatie.be/docs/laravel-permission)
and replace the simple `canAccessPanel()` check in `app/Models/User.php` with
role/permission checks. The current single-flag design is intentionally minimal
and is easy to grow into a role system without a rewrite.
