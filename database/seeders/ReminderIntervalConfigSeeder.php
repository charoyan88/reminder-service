<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReminderIntervalConfigSeeder extends Seeder
{
    public function run()
    {
        // Pre-expiration default intervals
        $preExpirationIntervals = [
            [
                'name' => 'One Week Before',
                'description' => 'Default reminder one week before expiration',
                'reminder_type' => 'pre_expiration',
                'days' => 7,
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Three Days Before',
                'description' => 'Default reminder three days before expiration',
                'reminder_type' => 'pre_expiration',
                'days' => 3,
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'One Day Before',
                'description' => 'Default reminder one day before expiration',
                'reminder_type' => 'pre_expiration',
                'days' => 1,
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Post-expiration default intervals
        $postExpirationIntervals = [
            [
                'name' => 'One Day After',
                'description' => 'Default reminder one day after expiration',
                'reminder_type' => 'post_expiration',
                'days' => 1,
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('reminder_interval_configs')->insert(array_merge($preExpirationIntervals, $postExpirationIntervals));
    }
} 