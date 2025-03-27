<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReminderConfigurationSeeder extends Seeder
{
    public function run()
    {
        // Pre-expiration reminders
        $preExpirationReminders = [
            [
                'name' => 'One Week Before',
                'description' => 'Send reminder one week before expiration',
                'reminder_type' => 'pre_expiration',
                'days_before_expiration' => 7,
                'order_type_codes' => json_encode(['TYPE_X', 'TYPE_Y']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Three Days Before',
                'description' => 'Send reminder three days before expiration',
                'reminder_type' => 'pre_expiration',
                'days_before_expiration' => 3,
                'order_type_codes' => json_encode(['TYPE_X', 'TYPE_Y']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'One Day Before',
                'description' => 'Send reminder one day before expiration',
                'reminder_type' => 'pre_expiration',
                'days_before_expiration' => 1,
                'order_type_codes' => json_encode(['TYPE_X', 'TYPE_Y']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Post-expiration reminders
        $postExpirationReminders = [
            [
                'name' => 'One Day After',
                'description' => 'Send reminder one day after expiration',
                'reminder_type' => 'post_expiration',
                'days_after_expiration' => 1,
                'order_type_codes' => json_encode(['TYPE_X', 'TYPE_Y']),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('reminder_configurations')->insert(array_merge($preExpirationReminders, $postExpirationReminders));
    }
} 