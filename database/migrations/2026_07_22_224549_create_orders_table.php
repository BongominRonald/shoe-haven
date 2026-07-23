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
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->decimal('total_amount', 12, 2);
            $table->string('payment_method', 32);
            $table->string('payment_phone', 32);
            $table->string('status', 32)->default('pending');
            $table->string('payment_status', 32)->default('pending');
            $table->string('transaction_id', 128)->nullable();
            $table->timestamp('payment_initiated_at')->nullable();
            $table->string('shipping_name', 255)->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('shipping_city', 128)->nullable();
            $table->string('shipping_phone', 32)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('user_id', 'idx_orders_user');
            $table->index('status', 'idx_orders_status');
            $table->index('created_at', 'idx_orders_created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};