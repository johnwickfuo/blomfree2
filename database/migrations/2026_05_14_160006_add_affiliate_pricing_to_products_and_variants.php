<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['products', 'product_variants', 'animals'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->decimal('affiliate_price', 15, 2)->nullable();
                $t->decimal('affiliate_commission', 15, 2)->nullable();
                $t->boolean('affiliate_enabled')->default(false);
            });
        }
    }

    public function down(): void
    {
        foreach (['products', 'product_variants', 'animals'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropColumn(['affiliate_price', 'affiliate_commission', 'affiliate_enabled']);
            });
        }
    }
};
