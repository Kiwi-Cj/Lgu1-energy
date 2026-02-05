<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Disable foreign key checks, truncate users, then re-enable
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('users')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        DB::table('users')->insert([
            [
                'full_name' => 'Admin User',
                'email' => 'admin@example.com',
                'username' => 'admin',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'department' => 'IT',
                'contact_number' => '09171234567',
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'last_login' => null,
            ],
            [
                'full_name' => 'Super Admin',
                'email' => 'johnchristianvaldez23@gmail.com',
                'username' => 'superadmin',
                'password' => Hash::make('superadmin123'),
                'role' => 'super admin',
                'department' => 'IT',
                'contact_number' => '09170000000',
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'last_login' => null,
            ],
            [
                'full_name' => 'Staff User',
                'email' => 'staff@example.com',
                'username' => 'staff',
                'password' => Hash::make('staff123'),
                'role' => 'staff',
                'department' => 'Operations',
                'contact_number' => '09171112222',
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'last_login' => null,
            ],
            [
                'full_name' => 'Engineer Officer',
                'email' => 'engineer@example.com',
                'username' => 'engineer',
                'password' => Hash::make('engineer123'),
                'role' => 'engineer officer',
                'department' => 'Engineering',
                'contact_number' => '09173334444',
                'status' => 'active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'last_login' => null,
            ]
        ]);
    }
}
