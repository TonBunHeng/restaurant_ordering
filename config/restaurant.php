<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Restaurant General Information
    |--------------------------------------------------------------------------
    */
    'name' => env('RESTAURANT_NAME', 'Royal Khmer Kitchen'),
    'full_name' => env('RESTAURANT_FULL_NAME', 'Royal Khmer Kitchen & Restaurant'),
    'tagline' => env('RESTAURANT_TAGLINE', 'Traditional Restaurant Ordering & Table Reservations'),
    'description' => env('RESTAURANT_DESCRIPTION', 'Simple, fresh and authentic dining hand-crafted daily using local Cambodian herbs and premium ingredients.'),
    'hero_subtitle' => env('RESTAURANT_HERO_SUBTITLE', 'Order fresh meals directly to your doorstep or reserve a dining table online. Hand-crafted daily using local Cambodian herbs, fresh Kampot pepper, and premium meats.'),
    
    /*
    |--------------------------------------------------------------------------
    | Contact & Location
    |--------------------------------------------------------------------------
    */
    'phone' => env('RESTAURANT_PHONE', '+855 12 888 999'),
    'address' => env('RESTAURANT_ADDRESS', 'Street 240, Daun Penh, Phnom Penh'),
    'email' => env('RESTAURANT_EMAIL', 'contact@royalkhmerkitchen.kh'),

    /*
    |--------------------------------------------------------------------------
    | Delivery & Pricing Rules
    |--------------------------------------------------------------------------
    */
    'currency' => env('RESTAURANT_CURRENCY', '$'),
    'delivery_fee' => (float) env('RESTAURANT_DELIVERY_FEE', 2.00),
    'free_delivery_threshold' => (float) env('RESTAURANT_FREE_DELIVERY_THRESHOLD', 30.00),

    /*
    |--------------------------------------------------------------------------
    | Reservation Time Slots & Dining Locations
    |--------------------------------------------------------------------------
    */
    'time_slots' => [
        '11:30', '12:00', '12:30', '13:00', '13:30',
        '17:30', '18:00', '18:30', '19:00', '19:30', '20:00', '20:30', '21:00'
    ],

    'table_locations' => [
        'Main Dining',
        'Window',
        'Outdoor Terrace',
        'VIP Room',
        'Private Booth'
    ],
];
