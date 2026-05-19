<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installment_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installment_plan_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();
            $table->decimal('amount', 15, 2);
            $table->string('payment_gateway');
            $table->string('payment_reference')->nullable();
            $table->string('payment_status')->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->boolean('is_down_payment')->default(false);
            $table->json('gateway_response')->nullable();
            $table->timestamps();

            $table->index(['installment_plan_id', 'payment_status']);
            $table->index('payment_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installment_payments');
    }
};
