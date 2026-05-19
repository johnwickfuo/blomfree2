<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lands', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('location_address');
            $table->string('state');
            $table->string('city_or_lga');
            $table->unsignedInteger('number_of_plots');
            $table->unsignedInteger('plot_size_sqm')->default(648);
            $table->decimal('price_per_plot', 15, 2);
            $table->string('price_label')->nullable();
            $table->text('description');
            $table->json('features');
            $table->json('close_to_landmarks')->nullable();
            $table->boolean('has_good_access_road')->default(true);
            $table->boolean('is_flood_free')->default(true);
            $table->boolean('installment_available')->default(false);
            $table->json('document_status');
            $table->enum('status', ['available', 'sold', 'reserved'])->default('available');
            $table->boolean('is_featured')->default(false);
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index('state');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lands');
    }
};
