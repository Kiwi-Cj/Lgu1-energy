<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (!$user) return;

        Notification::insert([
            [
                'user_id' => $user->id,
                'title' => 'Power Outage Reported',
                'message' => 'Power outage reported at Facility A.',
                'type' => 'incident',
                'created_at' => now()->subMinutes(5),
                'updated_at' => now()->subMinutes(5),
            ],
            [
                'user_id' => $user->id,
                'title' => 'Maintenance Reminder',
                'message' => 'Scheduled maintenance for Facility B on Feb 10, 2026.',
                'type' => 'maintenance',
                'created_at' => now()->subHour(),
                'updated_at' => now()->subHour(),
            ],
            [
                'user_id' => $user->id,
                'title' => 'Bill Due Alert',
                'message' => 'Meralco bill for Facility C is due in 3 days.',
                'type' => 'bill',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $user->id,
                'title' => 'Approval Request',
                'message' => 'Energy report for Facility D needs your approval.',
                'type' => 'approval',
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ],
            [
                'user_id' => $user->id,
                'title' => 'Account Update',
                'message' => 'Your password was changed successfully.',
                'type' => 'account',
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
        ]);
    }
}
