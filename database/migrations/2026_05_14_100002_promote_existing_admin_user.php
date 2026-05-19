<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Promote the admin account created during initial setup.
     *
     * Idempotent: the update simply sets the flag, and matches zero rows
     * harmlessly if the account does not exist (e.g. a fresh database).
     */
    public function up(): void
    {
        DB::table('users')
            ->where('email', 'admin@blomfree.com')
            ->update(['is_admin' => true]);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('email', 'admin@blomfree.com')
            ->update(['is_admin' => false]);
    }
};
