<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Restaurant Staff Management')</title>
    <!-- Bootstrap Icons CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/simple-style.css') }}">
</head>
<body>

    <div class="admin-wrapper">
        <!-- Admin Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">
                <i class="bi bi-shop"></i> {{ \App\Models\RestaurantSetting::get('name', 'Restaurant') }} Ops
            </div>

            <nav class="admin-nav">
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard*') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="{{ route('admin.kitchen.index') }}" class="{{ request()->routeIs('admin.kitchen*') ? 'active' : '' }}">
                    <i class="bi bi-fire"></i> Kitchen Queue
                </a>
                <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders*') ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i> Orders
                </a>
                <a href="{{ route('admin.reservations.index') }}" class="{{ request()->routeIs('admin.reservations*') ? 'active' : '' }}">
                    <i class="bi bi-calendar-check"></i> Bookings
                </a>
                <a href="{{ route('admin.tables.index') }}" class="{{ request()->routeIs('admin.tables*') ? 'active' : '' }}">
                    <i class="bi bi-grid-3x3-gap"></i> Dining Tables & Map
                </a>
                <a href="{{ route('admin.foods.index') }}" class="{{ request()->routeIs('admin.foods*') ? 'active' : '' }}">
                    <i class="bi bi-egg-fried"></i> Menu Dishes
                </a>
                <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories*') ? 'active' : '' }}">
                    <i class="bi bi-folder2-open"></i> Food Categories
                </a>
                <a href="{{ route('admin.promotions.index') }}" class="{{ request()->routeIs('admin.promotions*') ? 'active' : '' }}">
                    <i class="bi bi-tag"></i> Promotions
                </a>
                <a href="{{ route('admin.payments.index') }}" class="{{ request()->routeIs('admin.payments*') ? 'active' : '' }}">
                    <i class="bi bi-credit-card"></i> Payments
                </a>
                <a href="{{ route('admin.reviews.index') }}" class="{{ request()->routeIs('admin.reviews*') ? 'active' : '' }}">
                    <i class="bi bi-star"></i> Reviews
                </a>
                <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports*') ? 'active' : '' }}">
                    <i class="bi bi-graph-up"></i> Reports
                </a>
                <a href="{{ route('admin.customers.index') }}" class="{{ request()->routeIs('admin.customers*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Customers
                </a>
                <a href="{{ route('admin.activity-logs.index') }}" class="{{ request()->routeIs('admin.activity-logs*') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i> Activity Logs
                </a>
                <a href="{{ route('admin.settings.index') }}" class="{{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                    <i class="bi bi-gear"></i> Settings
                </a>
                @if(auth()->user()->role === 'super_admin')
                    <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                        <i class="bi bi-person-badge"></i> Staff & Roles
                    </a>
                @endif
                <hr style="border-color: #334155; margin: 10px 0;">
                <a href="{{ route('home') }}">
                    <i class="bi bi-arrow-left-circle"></i> Public Website
                </a>
            </nav>

            <div style="padding: 16px; border-top: 1px solid #334155; font-size: 12px; flex-shrink: 0;">
                <div style="color: #94a3b8; margin-bottom: 6px;">Logged in as:</div>
                <div style="font-weight: bold; color: #ffffff;"><i class="bi bi-person-circle"></i> {{ auth()->user()->name }}</div>
                <div style="color: #60a5fa; text-transform: capitalize; margin-bottom: 8px;">{{ str_replace('_', ' ', auth()->user()->role) }}</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm" style="width: 100%; font-size: 11px;"><i class="bi bi-box-arrow-right"></i> Logout</button>
                </form>
            </div>
        </aside>

        <!-- Admin Main Content Area -->
        <main class="admin-main">
            <div class="admin-topbar">
                <h1 style="font-size: 20px; font-weight: bold;">@yield('page-title', 'Management Portal')</h1>
                <div>
                    <a href="{{ route('menu.index') }}" target="_blank" class="btn btn-secondary btn-sm"><i class="bi bi-box-arrow-up-right"></i> View Customer Menu</a>
                </div>
            </div>

            <!-- Flash Alerts -->
            @if(session('success'))
                <div class="alert alert-success">
                    <span><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" style="border:none;background:none;cursor:pointer;font-weight:bold;">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">
                    <span><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</span>
                    <button type="button" onclick="this.parentElement.remove()" style="border:none;background:none;cursor:pointer;font-weight:bold;">&times;</button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-error">
                    <div>
                        <strong><i class="bi bi-exclamation-octagon-fill"></i> Please check the errors below:</strong>
                        <ul style="margin-left: 20px; margin-top: 4px;">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        // Auto dismiss all alert messages after 2 seconds
        document.addEventListener('DOMContentLoaded', function () {
            const alerts = document.querySelectorAll('.alert');
            if (alerts.length > 0) {
                setTimeout(function () {
                    alerts.forEach(function (alert) {
                        alert.classList.add('fade-out');
                        setTimeout(function () {
                            alert.remove();
                        }, 350);
                    });
                }, 2000);
            }
        });
    </script>
</body>
</html>
