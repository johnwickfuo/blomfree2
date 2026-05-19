<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('subsidiary', ['collections', 'gadgets']);
            $table->string('category');
            $table->string('short_description', 200);
            $table->text('description');
            $table->decimal('base_price', 15, 2);
            $table->decimal('compare_price', 15, 2)->nullable();
            $table->boolean('has_variants')->default(false);
            $table->unsignedInteger('stock')->nullable();
            $table->json('attribute_keys');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index('subsidiary');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
