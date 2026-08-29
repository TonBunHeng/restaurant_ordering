<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Restaurant Ordering & Table Reservations')</title>
    <!-- Bootstrap Icons CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('css/simple-style.css') }}">
</head>
<body>

    <!-- Header Navigation -->
    <header class="site-header">
        <div class="navbar">
            <a href="{{ route('home') }}" class="brand-logo">
                <i class="bi bi-shop" style="font-size: 20px;"></i> <span>{{ \App\Models\RestaurantSetting::get('name', 'Royal Khmer Kitchen') }}</span>
            </a>

            <div class="nav-container">
                <!-- Main Navigation -->
                <ul class="nav-links">
                    <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}"><i class="bi bi-house"></i> Home</a></li>
                    <li><a href="{{ route('menu.index') }}" class="{{ request()->routeIs('menu.*') ? 'active' : '' }}"><i class="bi bi-book"></i> Menu</a></li>
                    <li><a href="{{ route('reservations.create') }}" class="{{ request()->routeIs('reservations.create') ? 'active' : '' }}"><i class="bi bi-calendar-date"></i> Book a Table</a></li>
                    @auth
                        <li><a href="{{ route('chat.index') }}" class="{{ request()->routeIs('chat.*') ? 'active' : '' }}"><i class="bi bi-robot"></i> AI Assistant</a></li>
                    @endauth
                </ul>

                <!-- User Actions & Cart -->
                <ul class="nav-links nav-actions">
                    <li>
                        <a href="{{ route('cart.index') }}" class="{{ request()->routeIs('cart.*') ? 'active' : '' }}" style="font-weight: 600;">
                            <i class="bi bi-cart3"></i> Cart 
                            @php
                                $cartCount = is_array(session('cart')) ? array_sum(array_column(session('cart'), 'quantity')) : 0;
                            @endphp
                            @if($cartCount > 0)
                                <span class="cart-badge">{{ $cartCount }}</span>
                            @endif
                        </a>
                    </li>

                    @auth
                        <li class="nav-dropdown">
                            <button type="button" class="nav-dropdown-toggle" id="userMenuToggle" onclick="toggleUserDropdown(event)" aria-haspopup="true" aria-expanded="false">
                                <i class="bi bi-person-circle"></i> 
                                <span>{{ Str::limit(auth()->user()->name, 15) }}</span>
                                <i class="bi bi-chevron-down" style="font-size: 11px;"></i>
                            </button>
                            <div class="nav-dropdown-menu" id="userDropdownMenu">
                                <div style="padding: 8px 14px; font-size: 12px; color: var(--text-muted); border-bottom: 1px solid var(--border); margin-bottom: 4px;">
                                    <strong style="color: var(--text-main); font-size: 13px;">{{ auth()->user()->name }}</strong>
                                    <div style="font-size: 11px;">{{ auth()->user()->email }}</div>
                                </div>
                                <a href="{{ route('customer.dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
                                <a href="{{ route('orders.index') }}"><i class="bi bi-receipt"></i> My Orders</a>
                                <a href="{{ route('reservations.index') }}"><i class="bi bi-calendar-check"></i> My Table Bookings</a>
                                <a href="{{ route('favorites.index') }}"><i class="bi bi-heart"></i> Saved Favorites</a>
                                <a href="{{ route('profile.show') }}"><i class="bi bi-person"></i> Account Profile</a>
                                
                                @if(auth()->user()->isAdmin())
                                    <div class="nav-dropdown-divider"></div>
                                    <a href="{{ route('admin.dashboard') }}" style="color: #b45309; font-weight: 600;">
                                        <i class="bi bi-shield-lock"></i> Staff / Admin Portal
                                    </a>
                                @endif

                                <div class="nav-dropdown-divider"></div>
                                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                                    @csrf
                                    <button type="submit" style="color: var(--danger); font-weight: 500;">
                                        <i class="bi bi-box-arrow-right"></i> Log Out
                                    </button>
                                </form>
                            </div>
                        </li>
                    @else
                        <li><a href="{{ route('login') }}" class="btn btn-secondary btn-sm"><i class="bi bi-box-arrow-in-right"></i> Login</a></li>
                        <li><a href="{{ route('register') }}" class="btn btn-primary btn-sm"><i class="bi bi-person-plus"></i> Register</a></li>
                    @endauth
                </ul>
            </div>
        </div>
    </header>

    <!-- Main Body Container -->
    <main class="page-container">
        <!-- Flash Messages -->
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

        @if(session('info'))
            <div class="alert alert-warning">
                <span><i class="bi bi-info-circle-fill"></i> {{ session('info') }}</span>
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

    <!-- Simple Footer -->
    <footer class="site-footer">
        <div style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
                <strong>{{ \App\Models\RestaurantSetting::get('full_name', 'Royal Khmer Kitchen & Restaurant') }}</strong> — {{ \App\Models\RestaurantSetting::get('description', 'Simple, fresh and authentic dining.') }}
            </div>
            <div>
                <span><i class="bi bi-telephone"></i> Hotline: {{ \App\Models\RestaurantSetting::get('phone', '+855 12 888 999') }}</span> | 
                <span><i class="bi bi-geo-alt"></i> {{ \App\Models\RestaurantSetting::get('address', 'Daun Penh, Phnom Penh') }}</span>
            </div>
        </div>
    </footer>

    <script>
        function toggleUserDropdown(event) {
            if (event) {
                event.stopPropagation();
            }
            const menu = document.getElementById('userDropdownMenu');
            const toggle = document.getElementById('userMenuToggle');
            if (menu) {
                const isOpen = menu.classList.toggle('show');
                if (toggle) {
                    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                }
            }
        }

        document.addEventListener('click', function(event) {
            const menu = document.getElementById('userDropdownMenu');
            const toggle = document.getElementById('userMenuToggle');
            if (menu && menu.classList.contains('show')) {
                if (!menu.contains(event.target) && event.target !== toggle && !toggle.contains(event.target)) {
                    menu.classList.remove('show');
                    if (toggle) {
                        toggle.setAttribute('aria-expanded', 'false');
                    }
                }
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const menu = document.getElementById('userDropdownMenu');
                const toggle = document.getElementById('userMenuToggle');
                if (menu && menu.classList.contains('show')) {
                    menu.classList.remove('show');
                    if (toggle) {
                        toggle.setAttribute('aria-expanded', 'false');
                    }
                }
            }
        });

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
