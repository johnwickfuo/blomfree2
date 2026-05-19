<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->enum('subject', [
                'general', 'lands', 'kennel_farm', 'collections',
                'gadgets', 'partnership', 'media',
            ]);
            $table->text('message');
            $table->string('related_url')->nullable();
            $table->enum('status', ['new', 'in_progress', 'responded', 'closed'])->default('new');
            $table->timestamp('responded_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
