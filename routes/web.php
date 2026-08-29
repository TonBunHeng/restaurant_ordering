<?php

use Illuminate\Support\Facades\Route;

// Customer & Public Controllers
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ChatController;

// Admin Controllers
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminKitchenController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminFoodController;
use App\Http\Controllers\Admin\AdminTableController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminReservationController;
use App\Http\Controllers\Admin\AdminPromotionController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\AdminReportController;
use App\Http\Controllers\Admin\AdminActivityLogController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Admin\AdminUserController;

/*
|--------------------------------------------------------------------------
| Web Routes — Full-Stack Laravel Restaurant Ordering System
|--------------------------------------------------------------------------
*/

// Public Restaurant Pages
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
Route::get('/menu/{slug}', [MenuController::class, 'show'])->name('menu.show');

// Shopping Cart (Session-Based)
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/promo', [CartController::class, 'applyPromo'])->name('cart.promo');
Route::post('/cart/promo/remove', [CartController::class, 'removePromo'])->name('cart.promo.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Customer Routes
Route::middleware('auth')->group(function () {
    // Customer Dashboard
    Route::get('/customer', [CustomerDashboardController::class, 'index'])->name('customer.dashboard');

    // Checkout & Order Placement
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');

    // Customer Order History, Receipt & Cancellation
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{identifier}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

    // Customer Table Reservations
    Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/create', [ReservationController::class, 'create'])->name('reservations.create');
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
    Route::get('/reservations/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');
    Route::post('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');

    // Customer Favorites & Reviews
    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/toggle/{dish}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');
    Route::post('/favorites/{dish}/toggle', [FavoriteController::class, 'toggle']);
    Route::post('/dishes/{dish}/reviews', [ReviewController::class, 'store'])->name('reviews.store');

    // AI Food & Dining Companion Web Interface
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat', [ChatController::class, 'createConversation']);
    Route::post('/chat/conversation', [ChatController::class, 'createConversation'])->name('chat.create');
    Route::post('/chat/conversations/{conversation}/messages', [ChatController::class, 'sendMessage'])->name('chat.send');
    Route::post('/chat/{conversation}/send', [ChatController::class, 'sendMessage']);

    // Customer Profile
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});

// Protected Restaurant Admin & Staff Portal
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard Overview
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard.view');

    // Kitchen Order Display
    Route::get('/kitchen', [AdminKitchenController::class, 'index'])->name('kitchen.index');
    Route::put('/kitchen/{order}/status', [AdminKitchenController::class, 'updateStatus'])->name('kitchen.update-status');

    // Dining Tables Management & Table Map
    Route::get('/tables/map', [AdminTableController::class, 'map'])->name('tables.map');
    Route::resource('tables', AdminTableController::class)->except(['show']);

    // Menu Categories Management
    Route::resource('categories', AdminCategoryController::class)->except(['show']);

    // Foods / Dishes Management
    Route::resource('foods', AdminFoodController::class)->except(['show']);

    // Promotions / Discounts Management
    Route::resource('promotions', AdminPromotionController::class)->except(['show']);

    // Payments Management
    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
    Route::put('/payments/{payment}/status', [AdminPaymentController::class, 'updateStatus'])->name('payments.update-status');

    // Orders Management
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::get('/orders/{order}/print', [AdminOrderController::class, 'print'])->name('orders.print');
    Route::put('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');

    // Reservations Management
    Route::get('/reservations', [AdminReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/{reservation}', [AdminReservationController::class, 'show'])->name('reservations.show');
    Route::put('/reservations/{reservation}/status', [AdminReservationController::class, 'updateStatus'])->name('reservations.update-status');

    // Reviews Moderation
    Route::get('/reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::put('/reviews/{review}/status', [AdminReviewController::class, 'updateStatus'])->name('reviews.update-status');
    Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');

    // Reports
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');

    // Activity Logs
    Route::get('/activity-logs', [AdminActivityLogController::class, 'index'])->name('activity-logs.index');

    // Customer Management
    Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}', [AdminCustomerController::class, 'show'])->name('customers.show');
    Route::put('/customers/{customer}/status', [AdminCustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
    Route::put('/customers/{customer}/toggle-status', [AdminCustomerController::class, 'toggleStatus']);

    // Restaurant Business Settings
    Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [AdminSettingController::class, 'update'])->name('settings.update');

    // Users & Staff Governance (Super Admin)
    Route::resource('users', AdminUserController::class)->except(['show']);
    Route::put('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.update-role');
    Route::put('/users/{user}/status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
});