<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installment_plans', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('installable_type');
            $table->unsignedBigInteger('installable_id');
            $table->string('installable_label');
            $table->enum('subsidiary', ['lands', 'gadgets']);
            $table->string('status')->default('pending_approval');
            $table->decimal('total_amount', 15, 2);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('minimum_down_payment_amount', 15, 2);
            $table->unsignedTinyInteger('minimum_down_payment_percentage');
            $table->boolean('down_payment_paid')->default(false);
            $table->unsignedSmallInteger('maximum_length_months');
            $table->date('deadline')->nullable();
            $table->json('suggested_schedule')->nullable();
            $table->unsignedTinyInteger('forfeiture_percentage');
            $table->foreignId('affiliate_id')->nullable()->constrained()->nullOnDelete();
            $table->string('affiliate_code_used')->nullable();
            $table->decimal('affiliate_commission_locked', 15, 2)->default(0);
            $table->timestamp('terms_accepted_at');
            $table->string('terms_acceptance_ip', 45);
            $table->string('terms_version');
            $table->timestamp('requested_at');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamp('defaulted_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index(['installable_type', 'installable_id']);
            $table->index('status');
            $table->index('deadline');
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installment_plans');
    }
};
