<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 0. Update users role to support staff
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('user')->change();
        });

        // 1. Expand dishes table
        Schema::table('dishes', function (Blueprint $table) {
            if (!Schema::hasColumn('dishes', 'ingredients')) {
                $table->text('ingredients')->nullable()->after('description');
            }
            if (!Schema::hasColumn('dishes', 'allergens')) {
                $table->string('allergens')->nullable()->after('ingredients');
            }
            if (!Schema::hasColumn('dishes', 'dietary_info')) {
                $table->string('dietary_info')->nullable()->after('allergens');
            }
            if (!Schema::hasColumn('dishes', 'spicy_level')) {
                $table->unsignedTinyInteger('spicy_level')->default(0)->after('is_spicy');
            }
            if (!Schema::hasColumn('dishes', 'is_halal')) {
                $table->boolean('is_halal')->default(false)->after('is_vegetarian');
            }
        });

        // 2. Expand orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_status', 30)->default('pending')->change();
            $table->string('payment_status', 30)->default('pending')->change();
            $table->string('payment_method', 50)->default('cash_on_delivery')->change();

            if (!Schema::hasColumn('orders', 'order_type')) {
                $table->string('order_type', 30)->default('delivery')->after('order_number'); // dine_in, takeaway, delivery
            }
            if (!Schema::hasColumn('orders', 'table_number')) {
                $table->string('table_number', 50)->nullable()->after('order_type');
            }
            if (!Schema::hasColumn('orders', 'tax_amount')) {
                $table->decimal('tax_amount', 8, 2)->default(0.00)->after('discount_amount');
            }
            if (!Schema::hasColumn('orders', 'service_charge')) {
                $table->decimal('service_charge', 8, 2)->default(0.00)->after('tax_amount');
            }
            if (!Schema::hasColumn('orders', 'promo_code')) {
                $table->string('promo_code', 50)->nullable()->after('service_charge');
            }
            if (!Schema::hasColumn('orders', 'estimated_prep_time')) {
                $table->unsignedInteger('estimated_prep_time')->default(25)->after('notes');
            }
        });

        // 2b. Expand reservations status
        Schema::table('reservations', function (Blueprint $table) {
            $table->string('status', 30)->default('pending')->change();
        });

        // 3. Create promotions table
        if (!Schema::hasTable('promotions')) {
            Schema::create('promotions', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique()->index();
                $table->string('name')->nullable();
                $table->text('description')->nullable();
                $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
                $table->decimal('discount_value', 8, 2);
                $table->decimal('min_order_amount', 8, 2)->default(0.00);
                $table->decimal('max_discount_amount', 8, 2)->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->unsignedInteger('usage_limit')->nullable();
                $table->unsignedInteger('times_used')->default(0);
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        // 4. Create payments table
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
                $table->string('payment_method', 50);
                $table->decimal('amount', 10, 2);
                $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending')->index();
                $table->string('transaction_reference', 100)->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();
            });
        }

        // 5. Create activity_logs table
        if (!Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('action', 100)->index();
                $table->text('description');
                $table->string('subject_type', 100)->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();

                $table->index(['created_at', 'action']);
            });
        }

        // 6. Create restaurant_settings table
        if (!Schema::hasTable('restaurant_settings')) {
            Schema::create('restaurant_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key', 100)->unique()->index();
                $table->text('value')->nullable();
                $table->string('group', 50)->default('general');
                $table->timestamps();
            });
        }

        // 7. Create notifications table
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('restaurant_settings');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('promotions');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'order_type',
                'table_number',
                'tax_amount',
                'service_charge',
                'promo_code',
                'estimated_prep_time',
            ]);
        });

        Schema::table('dishes', function (Blueprint $table) {
            $table->dropColumn([
                'ingredients',
                'allergens',
                'dietary_info',
                'spicy_level',
                'is_halal',
            ]);
        });
    }
};
