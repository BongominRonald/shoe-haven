<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('previous_quantity');
            $table->integer('new_quantity');
            $table->integer('change_amount');
            $table->string('change_type', 32);
            $table->text('notes')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('created_at')->useCurrent();

            $table->index('product_id', 'idx_inventory_history_product');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_history');
    }
};