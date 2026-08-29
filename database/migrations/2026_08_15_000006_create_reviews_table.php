<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('dish_id')->constrained('dishes')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating'); // 1 to 5
            $table->string('title')->nullable();
            $table->text('comment');
            $table->enum('status', ['published', 'pending', 'hidden'])->default('published');
            $table->timestamps();

            $table->unique(['user_id', 'dish_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
