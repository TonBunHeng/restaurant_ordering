<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@aitourism.kh'],
            [
                'name' => 'Restaurant General Manager',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'avatar' => 'https://images.unsplash.com/photo-1577219491135-ce391730fb2c?w=300&auto=format&fit=crop&q=80',
                'phone' => '+855 12 888 999',
                'bio' => 'Head of Culinary Experience & Restaurant Operations.',
                'status' => 'active',
            ]
        );

        $this->command->info('✅ Default Admin created: ' . $admin->email);
    }
}