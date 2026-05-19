<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('animals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('listing_type', ['individual', 'pool']);
            $table->enum('category', ['dog', 'cat', 'rabbit', 'grasscutter', 'other']);
            $table->string('breed');
            $table->text('description');
            $table->string('origin')->nullable();
            $table->enum('sex', ['male', 'female', 'mixed'])->nullable();
            $table->string('age_text')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('typical_adult_size')->nullable();
            $table->string('temperament')->nullable();
            $table->json('vaccination_status')->nullable();
            $table->text('parents_info')->nullable();
            $table->decimal('price', 15, 2);
            $table->unsignedInteger('stock')->nullable();
            $table->enum('availability', ['available', 'reserved', 'sold', 'on_order'])->default('available');
            $table->boolean('supports_inspection')->default(true);
            $table->boolean('supports_online_purchase')->default(true);
            $table->json('highlights');
            $table->boolean('is_featured')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index('category');
            $table->index('listing_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('animals');
    }
};
