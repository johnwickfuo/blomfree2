<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('delivery_address')->nullable()->after('phone');
            $table->string('delivery_state')->nullable()->after('delivery_address');
            $table->string('delivery_lga')->nullable()->after('delivery_state');
            $table->string('bank_name')->nullable()->after('delivery_lga');
            $table->string('bank_account_number')->nullable()->after('bank_name');
            $table->string('bank_account_name')->nullable()->after('bank_account_number');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_address', 'delivery_state', 'delivery_lga',
                'bank_name', 'bank_account_number', 'bank_account_name',
            ]);
            $table->dropSoftDeletes();
        });
    }
};
