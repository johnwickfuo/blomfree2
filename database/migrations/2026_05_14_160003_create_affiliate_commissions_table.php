<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'available', 'reversed', 'withdrawn'])->default('pending');
            $table->timestamp('earned_at');
            $table->timestamp('available_at');
            $table->timestamp('reversed_at')->nullable();
            $table->text('reversal_reason')->nullable();
            $table->foreignId('withdrawal_id')->nullable()->constrained('affiliate_withdrawals')->nullOnDelete();
            $table->timestamps();

            $table->index(['affiliate_id', 'status']);
            $table->index('available_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_commissions');
    }
};
