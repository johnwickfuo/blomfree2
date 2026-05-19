<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installment_refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installment_plan_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->decimal('amount_paid_total', 15, 2);
            $table->decimal('forfeiture_amount', 15, 2);
            $table->decimal('refund_amount', 15, 2);
            $table->string('triggered_by');
            $table->string('status')->default('pending');
            $table->json('bank_snapshot')->nullable();
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
        Schema::dropIfExists('installment_refunds');
    }
};
