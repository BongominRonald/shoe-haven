<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('content');
            $table->tinyInteger('rating')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->integer('helpful_count')->default(0);
            $table->integer('not_helpful_count')->default(0);
            $table->text('images')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('product_id', 'idx_comments_product');
            $table->index('user_id', 'idx_comments_user');
            $table->index('status', 'idx_comments_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};