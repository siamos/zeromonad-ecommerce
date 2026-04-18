<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'     => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole('admin');

        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name'     => 'Manager User',
                'password' => Hash::make('password'),
            ]
        );
        $manager->assignRole('manager');

        $customer = User::firstOrCreate(
            ['email' => 'customer@example.com'],
            [
                'name'     => 'Test Customer',
                'password' => Hash::make('password'),
            ]
        );
        $customer->assignRole('customer');
    }
}
