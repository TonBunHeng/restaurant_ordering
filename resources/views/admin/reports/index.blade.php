@extends('layouts.admin')

@section('title', 'Business Reports')
@section('page-title', 'Restaurant Business Reports & Analytics')

@section('content')
<!-- Report Tabs -->
<div style="display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 1px solid var(--border); padding-bottom: 12px;">
    <a href="{{ route('admin.reports.index', ['tab' => 'sales']) }}" class="btn {{ $tab === 'sales' ? 'btn-primary' : 'btn-secondary' }}">
        <i class="bi bi-currency-dollar"></i> Sales & Revenue Report
    </a>
    <a href="{{ route('admin.reports.index', ['tab' => 'food']) }}" class="btn {{ $tab === 'food' ? 'btn-primary' : 'btn-secondary' }}">
        <i class="bi bi-egg-fried"></i> Food & Menu Report
    </a>
    <a href="{{ route('admin.reports.index', ['tab' => 'reservations']) }}" class="btn {{ $tab === 'reservations' ? 'btn-primary' : 'btn-secondary' }}">
        <i class="bi bi-calendar-check"></i> Table Reservations Report
    </a>
</div>

@if($tab === 'sales')
    <!-- Period Filter Form -->
    <div class="card" style="margin-bottom: 20px;">
        <form method="GET" action="{{ route('admin.reports.index') }}" style="display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;">
            <input type="hidden" name="tab" value="sales">
            <div>
                <label for="period" style="font-size: 12px; font-weight: bold; display: block; margin-bottom: 4px;">Timeframe:</label>
                <select name="period" id="period" class="form-select" onchange="toggleCustomDates(this.value)">
                    <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Today</option>
                    <option value="yesterday" {{ $period === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                    <option value="this_week" {{ $period === 'this_week' ? 'selected' : '' }}>This Week</option>
                    <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>This Month</option>
                    <option value="custom" {{ $period === 'custom' ? 'selected' : '' }}>Custom Date Range</option>
                </select>
            </div>

            <div id="custom-date-start" style="display: {{ $period === 'custom' ? 'block' : 'none' }};">
                <label for="start_date" style="font-size: 12px; font-weight: bold; display: block; margin-bottom: 4px;">From:</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date', $start->format('Y-m-d')) }}">
            </div>

            <div id="custom-date-end" style="display: {{ $period === 'custom' ? 'block' : 'none' }};">
                <label for="end_date" style="font-size: 12px; font-weight: bold; display: block; margin-bottom: 4px;">To:</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date', $end->format('Y-m-d')) }}">
            </div>

            <button type="submit" class="btn btn-secondary"><i class="bi bi-filter"></i> Apply Filter</button>
            <button type="button" onclick="window.print()" class="btn btn-secondary"><i class="bi bi-printer"></i> Print Report</button>
        </form>
    </div>

    <!-- Sales Stat Cards -->
    <div class="grid grid-4" style="gap: 16px; margin-bottom: 24px;">
        <div class="card" style="padding: 16px;">
            <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase;">Total Revenue</div>
            <div style="font-size: 24px; font-weight: 800; color: #15803d; margin-top: 4px;">${{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase;">Total Orders Placed</div>
            <div style="font-size: 24px; font-weight: 800; color: var(--primary); margin-top: 4px;">{{ $totalOrders }}</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase;">Avg Order Value</div>
            <div style="font-size: 24px; font-weight: 800; color: #b45309; margin-top: 4px;">${{ number_format($avgOrderValue, 2) }}</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase;">Completed vs Cancelled</div>
            <div style="font-size: 18px; font-weight: 800; margin-top: 6px;">
                <span style="color: #15803d;">{{ $completedOrders }} OK</span> / <span style="color: #b91c1c;">{{ $cancelledOrders }} Cancelled</span>
            </div>
        </div>
    </div>

    <!-- Orders In Range Table -->
    <div class="card">
        <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 14px;">Recent Orders in Selected Period ({{ $start->format('M d, Y') }} - {{ $end->format('M d, Y') }})</h3>
        <table class="simple-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date & Time</th>
                    <th>Customer</th>
                    <th>Type</th>
                    <th>Total Amount</th>
                    <th>Payment</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ordersList as $ord)
                    <tr>
                        <td><strong><a href="{{ route('admin.orders.show', $ord->id) }}">#{{ $ord->order_number }}</a></strong></td>
                        <td>{{ $ord->created_at->format('M d, Y H:i') }}</td>
                        <td>{{ $ord->customer_name }}</td>
                        <td>{{ $ord->formatted_order_type }}</td>
                        <td><strong>${{ number_format($ord->total_amount, 2) }}</strong></td>
                        <td><span class="badge {{ $ord->payment_status === 'paid' ? 'badge-success' : 'badge-pending' }}">{{ ucfirst($ord->payment_status) }}</span></td>
                        <td><span class="badge badge-info">{{ ucfirst($ord->order_status) }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px; color: var(--text-muted);">No orders recorded in this timeframe.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@elseif($tab === 'food')
    <div class="grid grid-2" style="gap: 20px;">
        <!-- Most Ordered Dishes -->
        <div class="card">
            <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 14px; color: #15803d;">
                <i class="bi bi-trophy"></i> Top 10 Most Ordered Dishes
            </h2>
            <table class="simple-table">
                <thead>
                    <tr>
                        <th>Dish Name</th>
                        <th style="text-align: center;">Total Units Sold</th>
                        <th style="text-align: right;">Total Sales ($)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($popularDishes as $item)
                        <tr>
                            <td><strong>{{ $item->dish_name }}</strong></td>
                            <td style="text-align: center;"><span class="badge badge-success">{{ $item->total_qty }} sold</span></td>
                            <td style="text-align: right;"><strong>${{ number_format($item->total_sales, 2) }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 20px; color: var(--text-muted);">No sales data available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Least Ordered / Needs Attention -->
        <div class="card">
            <h2 style="font-size: 16px; font-weight: bold; margin-bottom: 14px; color: #b45309;">
                <i class="bi bi-arrow-down-circle"></i> Least Ordered Dishes
            </h2>
            <table class="simple-table">
                <thead>
                    <tr>
                        <th>Dish Name</th>
                        <th>Price</th>
                        <th style="text-align: center;">Total Orders</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leastOrderedDishes as $dish)
                        <tr>
                            <td><strong><a href="{{ route('admin.foods.edit', $dish->id) }}">{{ $dish->name }}</a></strong></td>
                            <td>${{ number_format($dish->price, 2) }}</td>
                            <td style="text-align: center;"><span class="badge badge-pending">{{ $dish->order_items_count }} times</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; padding: 20px; color: var(--text-muted);">No dishes found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@elseif($tab === 'reservations')
    <div class="grid grid-4" style="gap: 16px; margin-bottom: 24px;">
        <div class="card" style="padding: 16px;">
            <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase;">Total Reservations</div>
            <div style="font-size: 24px; font-weight: 800; color: var(--primary); margin-top: 4px;">{{ $totalReservations }}</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase;">Confirmed Bookings</div>
            <div style="font-size: 24px; font-weight: 800; color: #15803d; margin-top: 4px;">{{ $confirmedReservations }}</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase;">Completed Dining</div>
            <div style="font-size: 24px; font-weight: 800; color: #2563eb; margin-top: 4px;">{{ $completedReservations }}</div>
        </div>
        <div class="card" style="padding: 16px;">
            <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase;">Cancelled / Rejected</div>
            <div style="font-size: 18px; font-weight: 800; margin-top: 6px;">
                <span style="color: #b91c1c;">{{ $cancelledReservations }} Cancelled</span> / <span style="color: #64748b;">{{ $rejectedReservations }} Rejected</span>
            </div>
        </div>
    </div>
@endif

<script>
    function toggleCustomDates(val) {
        var start = document.getElementById('custom-date-start');
        var end = document.getElementById('custom-date-end');
        if (val === 'custom') {
            start.style.display = 'block';
            end.style.display = 'block';
        } else {
            start.style.display = 'none';
            end.style.display = 'none';
        }
    }
</script>
@endsection
