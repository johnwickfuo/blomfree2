<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->morphs('inspectable');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->date('preferred_date');
            $table->string('preferred_time_slot');
            $table->date('alternate_date')->nullable();
            $table->unsignedInteger('party_size')->default(1);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed', 'no_show'])->default('pending');
            $table->text('meeting_address')->nullable();
            $table->text('meeting_instructions')->nullable();
            $table->text('admin_notes')->nullable();
            $table->string('reference')->unique();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
