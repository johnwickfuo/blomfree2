<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliate_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->decimal('amount', 15, 2);
            $table->decimal('fee', 15, 2);
            $table->decimal('net_amount', 15, 2);
            $table->json('bank_snapshot');
            $table->enum('status', ['pending', 'approved', 'paid', 'rejected'])->default('pending');
            $table->text('admin_notes')->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamp('requested_at');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_withdrawals');
    }
};
