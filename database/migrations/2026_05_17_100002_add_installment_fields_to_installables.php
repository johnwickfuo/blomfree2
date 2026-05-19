<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lands', function (Blueprint $table) {
            $table->boolean('installment_enabled')->default(false)->after('installment_available');
            $table->unsignedTinyInteger('installment_minimum_down_payment_percentage')->nullable()->after('installment_enabled');
            $table->unsignedSmallInteger('installment_maximum_length_months')->nullable()->after('installment_minimum_down_payment_percentage');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->boolean('installment_enabled')->default(false)->after('affiliate_enabled');
            $table->unsignedTinyInteger('installment_minimum_down_payment_percentage')->nullable()->after('installment_enabled');
            $table->unsignedSmallInteger('installment_maximum_length_months')->nullable()->after('installment_minimum_down_payment_percentage');
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->boolean('installment_enabled')->default(false)->after('affiliate_enabled');
            $table->unsignedTinyInteger('installment_minimum_down_payment_percentage')->nullable()->after('installment_enabled');
            $table->unsignedSmallInteger('installment_maximum_length_months')->nullable()->after('installment_minimum_down_payment_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('lands', function (Blueprint $table) {
            $table->dropColumn(['installment_enabled', 'installment_minimum_down_payment_percentage', 'installment_maximum_length_months']);
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['installment_enabled', 'installment_minimum_down_payment_percentage', 'installment_maximum_length_months']);
        });
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropColumn(['installment_enabled', 'installment_minimum_down_payment_percentage', 'installment_maximum_length_months']);
        });
    }
};
