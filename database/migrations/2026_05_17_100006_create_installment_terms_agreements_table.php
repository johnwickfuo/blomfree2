<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installment_terms_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('installment_plan_id')->constrained()->cascadeOnDelete();
            $table->string('terms_version');
            $table->text('terms_text_snapshot');
            $table->string('ip_address', 45);
            $table->string('user_agent', 500);
            $table->timestamp('accepted_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installment_terms_agreements');
    }
};
