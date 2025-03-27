<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderTypeSeeder extends Seeder
{
    public function run()
    {
        DB::table('order_types')->insert([
            [
                'name' => 'Type X',
                'code' => 'TYPE_X',
                'description' => 'Expires 1 year after the application date',
                'expiration_type' => 'fixed_period',
                'expiration_period_months' => 12,
                'requires_renewal' => true,
                'allows_early_renewal' => true,
                'early_renewal_days' => 30,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Type Y',
                'code' => 'TYPE_Y',
                'description' => 'Expires on December 31 of the current year',
                'expiration_type' => 'year_end',
                'requires_renewal' => true,
                'allows_early_renewal' => true,
                'early_renewal_days' => 30,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
} 