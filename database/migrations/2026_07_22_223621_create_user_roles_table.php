<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', ['admin', 'user'])->default('user');

            $table->unique(['user_id', 'role'], 'uq_user_roles');
            $table->index('user_id', 'idx_user_roles_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};