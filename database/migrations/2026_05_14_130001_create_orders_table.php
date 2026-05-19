<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->text('delivery_address');
            $table->string('delivery_state');
            $table->string('delivery_lga')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->enum('delivery_method', ['delivery', 'pickup']);
            $table->foreignId('shipping_zone_id')->nullable()->constrained('shipping_zones')->nullOnDelete();
            $table->decimal('shipping_fee', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('total', 15, 2);
            $table->enum('payment_gateway', ['paystack', 'flutterwave']);
            $table->string('payment_reference')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'cancelled'])->default('pending');
            $table->enum('order_status', [
                'pending_payment', 'paid', 'processing', 'shipped',
                'delivered', 'cancelled', 'refunded',
            ])->default('pending_payment');
            $table->text('tracking_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('placed_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('order_status');
            $table->index('customer_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
