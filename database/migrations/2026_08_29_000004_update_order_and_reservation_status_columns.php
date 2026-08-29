<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('user')->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_status', 30)->default('pending')->change();
            $table->string('payment_status', 30)->default('pending')->change();
            $table->string('payment_method', 50)->default('cash_on_delivery')->change();
        });

        Schema::table('reservations', function (Blueprint $table) {
            $table->string('status', 30)->default('pending')->change();
        });
    }

    public function down(): void
    {
        // No-op
    }
};
