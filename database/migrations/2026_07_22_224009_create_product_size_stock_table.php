<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_size_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('size', 8);
            $table->integer('quantity')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'size'], 'uq_product_size_stock');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_size_stock');
    }
};