<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dishes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('name')->index();
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('short_description', 255)->nullable();
            $table->decimal('price', 8, 2);
            $table->decimal('discount_price', 8, 2)->nullable();
            $table->integer('preparation_time')->default(20); // in minutes
            $table->integer('calories')->nullable();
            $table->boolean('is_spicy')->default(false);
            $table->boolean('is_vegetarian')->default(false);
            $table->boolean('is_chef_special')->default(false);
            $table->boolean('is_available')->default(true);
            $table->string('cover_image')->nullable();
            $table->json('images')->nullable();
            $table->decimal('average_rating', 3, 2)->default(5.00);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->enum('status', ['published', 'draft', 'archived'])->default('published');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'status', 'is_available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dishes');
    }
};
