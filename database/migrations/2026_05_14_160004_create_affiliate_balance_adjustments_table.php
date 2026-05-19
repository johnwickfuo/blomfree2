<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_balance_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['credit', 'debit']);
            $table->decimal('amount', 15, 2);
            $table->text('reason');
            $table->timestamps();

            $table->index('affiliate_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_balance_adjustments');
    }
};
