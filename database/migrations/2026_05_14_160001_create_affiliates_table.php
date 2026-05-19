<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->enum('status', ['active', 'suspended'])->default('active');
            $table->string('whatsapp_number')->nullable();
            $table->json('social_handles')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->decimal('pending_balance', 15, 2)->default(0);
            $table->decimal('available_balance', 15, 2)->default(0);
            $table->decimal('total_earned', 15, 2)->default(0);
            $table->decimal('total_withdrawn', 15, 2)->default(0);
            $table->timestamp('suspended_at')->nullable();
            $table->text('suspension_reason')->nullable();
            $table->timestamp('joined_at');
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliates');
    }
};
