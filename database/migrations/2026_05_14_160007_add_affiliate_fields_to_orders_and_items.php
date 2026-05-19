<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('affiliate_id')->nullable()->after('shipping_zone_id')->constrained()->nullOnDelete();
            $table->string('affiliate_code_used')->nullable()->after('affiliate_id');
            $table->decimal('affiliate_discount_total', 15, 2)->default(0)->after('affiliate_code_used');
            $table->decimal('affiliate_commission_total', 15, 2)->default(0)->after('affiliate_discount_total');
            $table->timestamp('delivered_at')->nullable()->after('paid_at');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('normal_unit_price', 15, 2)->default(0)->after('unit_price');
            $table->decimal('affiliate_unit_commission', 15, 2)->default(0)->after('normal_unit_price');
            $table->boolean('affiliate_eligible')->default(false)->after('affiliate_unit_commission');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('affiliate_id');
            $table->dropColumn(['affiliate_code_used', 'affiliate_discount_total', 'affiliate_commission_total', 'delivered_at']);
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['normal_unit_price', 'affiliate_unit_commission', 'affiliate_eligible']);
        });
    }
};
