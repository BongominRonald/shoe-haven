<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_sections', function (Blueprint $table) {
            $table->id();
            $table->string('headline')->default('Step Into Comfort & Style');
            $table->text('subtitle')->default('Premium shoes for every stride — from everyday sneakers to statement heels. Quality craftsmanship, unbeatable prices, delivered to your door.');
            $table->string('button_text')->default('Shop Now');
            $table->string('button_url')->default('/shop');
            $table->string('secondary_button_text')->default('Explore Collection');
            $table->string('secondary_button_url')->default('/shop');
            $table->string('image')->default('images/hero-banner.jpg');
            $table->json('stats')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_sections');
    }
};
