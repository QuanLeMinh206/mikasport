<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            [
                'full_name' => 'Admin User',
                'user_name' => 'admin',
                'email' => 'admin@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password123'), // Mã hóa mật khẩu
                'phone' => '0123456789',
                'role' => 1, // 1: Admin, 0: User
                'img' => 'default.jpg',
                'address' => '123 Main Street',
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'full_name' => 'Test User',
                'user_name' => 'testuser',
                'email' => 'test@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('test123456'),
                'phone' => '0987654321',
                'role' => 0,
                'img' => 'default.jpg',
                'address' => '456 Test Street',
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}