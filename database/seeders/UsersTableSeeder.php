<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $users = [
            [
                'facility_id' => null,
                'full_name' => 'Super Admin',
                'email' => 'admin@example.com',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'role' => 'super admin',
                'status' => 'active',
                'contact_number' => '09123456789',
                'department' => 'Admin',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'facility_id' => null,
                'full_name' => 'Super Admin 2',
                'email' => 'johnchristianvaldez23@gmail.com',
                'username' => 'admin2',
                'password' => Hash::make('password'),
                'role' => 'super admin',
                'status' => 'active',
                'contact_number' => '09123456789',
                'department' => 'Admin',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'facility_id' => null,
                'full_name' => 'Energy Officer',
                'email' => 'energy@example.com',
                'username' => 'energy',
                'password' => Hash::make('password'),
                'role' => 'energy_officer',
                'status' => 'active',
                'contact_number' => '09123456789',
                'department' => 'Energy',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'facility_id' => null,
                'full_name' => 'Staff User',
                'email' => 'staff@example.com',
                'username' => 'staff',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'status' => 'active',
                'contact_number' => '09123456789',
                'department' => 'Staff',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('users')->upsert(
            $users,
            ['email'],
            [
                'facility_id',
                'full_name',
                'username',
                'password',
                'role',
                'status',
                'contact_number',
                'department',
                'updated_at',
            ]
        );
    }
}
