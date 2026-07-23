<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rate_limit_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('bucket', 150);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['bucket', 'created_at'], 'idx_rate_limit_bucket_time');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_limit_attempts');
    }
};