<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DishController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\ChatAiController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\UserController;
use App\Models\Promotion;
use App\Models\RestaurantSetting;
use App\Models\RestaurantTable;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes - FastBite Restaurant Ordering & Table Reservations (v1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // 1. Authentication Endpoints
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
            Route::put('/profile', [AuthController::class, 'updateProfile']);
        });
    });

    // 2. Menu Categories (Public)
    Route::get('/categories', [CategoryController::class, 'index']);

    // 3. Menu Dishes (Public)
    Route::get('/dishes', [DishController::class, 'index']);
    Route::get('/dishes/featured', [DishController::class, 'featured']);
    Route::get('/dishes/{slug}', [DishController::class, 'show']);
    Route::get('/dishes/{dish}/reviews', [ReviewController::class, 'index']);

    // 4. Restaurant Tables & Settings (Public)
    Route::get('/tables', function () {
        $tables = RestaurantTable::where('status', '!=', 'unavailable')->orderBy('table_number', 'asc')->get();
        return response()->json(['success' => true, 'data' => $tables]);
    });

    Route::get('/settings', function () {
        return response()->json(['success' => true, 'data' => RestaurantSetting::getAll()]);
    });

    // Promo code validation
    Route::post('/promotions/validate', function (Request $request) {
        $validated = $request->validate([
            'code' => 'required|string',
            'subtotal' => 'required|numeric|min:0',
        ]);

        $promo = Promotion::where('code', strtoupper(trim($validated['code'])))->first();
        if (!$promo) {
            return response()->json(['success' => false, 'message' => 'Invalid promo code.'], 404);
        }

        $errorMsg = null;
        if (!$promo->isValidForAmount((float) $validated['subtotal'], $errorMsg)) {
            return response()->json(['success' => false, 'message' => $errorMsg ?: 'Promo code cannot be applied.'], 422);
        }

        $discount = $promo->calculateDiscount((float) $validated['subtotal']);

        return response()->json([
            'success' => true,
            'data' => [
                'code' => $promo->code,
                'discount_type' => $promo->discount_type,
                'discount_value' => $promo->discount_value,
                'calculated_discount' => $discount,
            ],
        ]);
    });

    // 5. Customer Orders (Public / Authenticated)
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{orderNumber}', [OrderController::class, 'show']);

    // 6. Table Reservations (Public / Authenticated)
    Route::post('/reservations', [ReservationController::class, 'store']);

    // 7. Authenticated User Protected Routes
    Route::middleware('auth:sanctum')->group(function () {
        
        // Orders & Reservations History
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/user/orders', [OrderController::class, 'index']);
        Route::get('/reservations', [ReservationController::class, 'index']);
        Route::get('/user/reservations', [ReservationController::class, 'index']);

        // Dish Reviews
        Route::get('/reviews', [ReviewController::class, 'index']);
        Route::post('/reviews', [ReviewController::class, 'store']);
        Route::post('/dishes/{dish}/reviews', [ReviewController::class, 'store']);
        Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);

        // Favorites
        Route::get('/favorites', [FavoriteController::class, 'index']);
        Route::post('/favorites', [FavoriteController::class, 'store']);
        Route::post('/favorites/toggle/{dish}', [FavoriteController::class, 'toggle']);
        Route::delete('/favorites/{dish}', [FavoriteController::class, 'destroy']);

        // AI Food & Menu Companion Conversations
        Route::get('/conversations', [ChatAiController::class, 'index']);
        Route::post('/conversations', [ChatAiController::class, 'store']);
        Route::get('/conversations/{conversation}', [ChatAiController::class, 'show']);
        Route::post('/conversations/{conversation}/messages', [ChatAiController::class, 'sendMessage']);
        Route::delete('/conversations/{conversation}', [ChatAiController::class, 'destroy']);
    });

    // 8. Admin Console Routes (Role: staff, admin, super_admin)
    Route::middleware(['auth:sanctum', 'role:staff,admin,super_admin'])->prefix('admin')->group(function () {
        
        // Dashboard Analytics
        Route::get('/dashboard/stats', [AdminDashboardController::class, 'stats']);

        // Manage Categories
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

        // Manage Dishes
        Route::post('/dishes', [DishController::class, 'store']);
        Route::put('/dishes/{dish}', [DishController::class, 'update']);
        Route::delete('/dishes/{dish}', [DishController::class, 'destroy']);

        // Manage Orders
        Route::get('/orders', [OrderController::class, 'adminIndex']);
        Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus']);

        // Manage Table Reservations
        Route::get('/reservations', [ReservationController::class, 'adminIndex']);
        Route::put('/reservations/{reservation}/status', [ReservationController::class, 'updateStatus']);
        Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy']);

        // Super Admin: User Management
        Route::middleware('role:super_admin')->group(function () {
            Route::get('/users', [UserController::class, 'index']);
            Route::post('/users', [UserController::class, 'store']);
            Route::put('/users/{user}', [UserController::class, 'update']);
            Route::delete('/users/{user}', [UserController::class, 'destroy']);
        });
    });
});
