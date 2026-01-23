<?php
// Run this with: php insert_default_users.php
use Illuminate\Support\Facades\Hash;
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

// Insert default users if not exists
function createUserIfNotExists($data) {
    if (!User::where('username', $data['username'])->exists()) {
        User::create($data);
        echo "Created user: {$data['username']}\n";
    } else {
        echo "User already exists: {$data['username']}\n";
    }
}

createUserIfNotExists([
    'full_name' => 'Admin User',
    'email' => 'admin@example.com',
    'username' => 'admin',
    'password' => bcrypt('admin123'),
    'role' => 'admin',
    'department' => 'IT',
    'contact_number' => '09171234567',
    'status' => 'active',
    'last_login' => null,
]);

createUserIfNotExists([
    'full_name' => 'Staff User',
    'email' => 'staff@example.com',
    'username' => 'staff',
    'password' => bcrypt('staff123'),
    'role' => 'staff',
    'department' => 'Operations',
    'contact_number' => '09170000001',
    'status' => 'active',
    'last_login' => null,
]);

createUserIfNotExists([
    'full_name' => 'Energy Officer',
    'email' => 'eeco@example.com',
    'username' => 'eeco',
    'password' => bcrypt('eeco123'),
    'role' => 'energy_officer',
    'department' => 'Energy',
    'contact_number' => '09170000002',
    'status' => 'active',
    'last_login' => null,
]);

echo "Done.\n";