<?php

namespace Tests\Unit;

use App\Models\Business;
use App\Models\Order;
use App\Models\OrderType;
use App\Models\ReminderConfiguration;
use App\Models\ReminderIntervalConfig;
use App\Services\ReminderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReminderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $service;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ReminderService::class);
    }
    
    /** @test */
    public function it_can_determine_reminder_dates_from_configurable_intervals()
    {
        // Create order type
        $orderType = OrderType::create([
            'name' => 'Type X',
            'code' => 'TYPE_X',
            'expiration_type' => 'fixed_period',
            'expiration_period_months' => 12,
            'is_active' => true,
        ]);
        
        // Create intervals and configurations
        $oneWeekInterval = ReminderIntervalConfig::create([
            'name' => 'One Week Before',
            'reminder_type' => 'pre_expiration',
            'days' => 7,
            'is_active' => true,
        ]);
        
        ReminderConfiguration::create([
            'name' => $oneWeekInterval->name,
            'reminder_type' => 'pre_expiration',
            'days_before_expiration' => $oneWeekInterval->days,
            'order_type_codes' => json_encode(['TYPE_X']),
            'is_active' => true,
        ]);
        
        // Create business and order
        $business = Business::create([
            'name' => 'Test Business',
            'contact_email' => 'test@example.com',
            'active' => true,
        ]);
        
        $expirationDate = Carbon::parse('2024-12-31');
        $order = Order::create([
            'business_id' => $business->id,
            'order_type_id' => $orderType->id,
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'application_date' => Carbon::now(),
            'expiration_date' => $expirationDate,
            'status' => 'active',
        ]);
        
        // Call the method to get reminder dates for this order
        $reminderDates = $this->service->calculateReminderDates($order);
        
        // Assert the dates are calculated correctly
        $expected = [
            'pre_expiration' => [
                $expirationDate->copy()->subDays(7)->startOfDay()->format('Y-m-d'),
            ],
            'post_expiration' => [],
        ];
        
        // Convert the actual dates to a comparable format
        $actual = [
            'pre_expiration' => [],
            'post_expiration' => [],
        ];
        
        foreach ($reminderDates['pre_expiration'] as $date) {
            $actual['pre_expiration'][] = $date->format('Y-m-d');
        }
        
        foreach ($reminderDates['post_expiration'] as $date) {
            $actual['post_expiration'][] = $date->format('Y-m-d');
        }
        
        $this->assertEquals($expected, $actual);
    }
    
    /** @test */
    public function it_should_handle_multiple_intervals_of_different_types()
    {
        // Create order type
        $orderType = OrderType::create([
            'name' => 'Type X',
            'code' => 'TYPE_X',
            'expiration_type' => 'fixed_period',
            'expiration_period_months' => 12,
            'is_active' => true,
        ]);
        
        // Create pre-expiration intervals
        $preIntervals = [
            ['name' => 'One Week Before', 'days' => 7],
            ['name' => 'Three Days Before', 'days' => 3],
            ['name' => 'One Day Before', 'days' => 1],
        ];
        
        foreach ($preIntervals as $interval) {
            $config = ReminderIntervalConfig::create([
                'name' => $interval['name'],
                'reminder_type' => 'pre_expiration',
                'days' => $interval['days'],
                'is_active' => true,
            ]);
            
            ReminderConfiguration::create([
                'name' => $config->name,
                'reminder_type' => 'pre_expiration',
                'days_before_expiration' => $config->days,
                'order_type_codes' => json_encode(['TYPE_X']),
                'is_active' => true,
            ]);
        }
        
        // Create post-expiration intervals
        $postInterval = ReminderIntervalConfig::create([
            'name' => 'One Day After',
            'reminder_type' => 'post_expiration',
            'days' => 1,
            'is_active' => true,
        ]);
        
        ReminderConfiguration::create([
            'name' => $postInterval->name,
            'reminder_type' => 'post_expiration',
            'days_after_expiration' => $postInterval->days,
            'order_type_codes' => json_encode(['TYPE_X']),
            'is_active' => true,
        ]);
        
        // Create business and order
        $business = Business::create([
            'name' => 'Test Business',
            'contact_email' => 'test@example.com',
            'active' => true,
        ]);
        
        $expirationDate = Carbon::parse('2024-12-31');
        $order = Order::create([
            'business_id' => $business->id,
            'order_type_id' => $orderType->id,
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'application_date' => Carbon::now(),
            'expiration_date' => $expirationDate,
            'status' => 'active',
        ]);
        
        // Call the method to get reminder dates for this order
        $reminderDates = $this->service->calculateReminderDates($order);
        
        // Assert the dates are calculated correctly
        $expected = [
            'pre_expiration' => [
                $expirationDate->copy()->subDays(7)->startOfDay()->format('Y-m-d'),
                $expirationDate->copy()->subDays(3)->startOfDay()->format('Y-m-d'),
                $expirationDate->copy()->subDays(1)->startOfDay()->format('Y-m-d'),
            ],
            'post_expiration' => [
                $expirationDate->copy()->addDays(1)->startOfDay()->format('Y-m-d'),
            ],
        ];
        
        // Convert the actual dates to a comparable format
        $actual = [
            'pre_expiration' => [],
            'post_expiration' => [],
        ];
        
        foreach ($reminderDates['pre_expiration'] as $date) {
            $actual['pre_expiration'][] = $date->format('Y-m-d');
        }
        
        foreach ($reminderDates['post_expiration'] as $date) {
            $actual['post_expiration'][] = $date->format('Y-m-d');
        }
        
        // Sort both arrays to ensure consistent comparison
        sort($expected['pre_expiration']);
        sort($expected['post_expiration']);
        sort($actual['pre_expiration']);
        sort($actual['post_expiration']);
        
        $this->assertEquals($expected, $actual);
    }
    
    /** @test */
    public function it_respects_order_type_when_filtering_reminder_configurations()
    {
        // Create two order types
        $typeX = OrderType::create([
            'name' => 'Type X',
            'code' => 'TYPE_X',
            'expiration_type' => 'fixed_period',
            'expiration_period_months' => 12,
            'is_active' => true,
        ]);
        
        $typeY = OrderType::create([
            'name' => 'Type Y',
            'code' => 'TYPE_Y',
            'expiration_type' => 'year_end',
            'is_active' => true,
        ]);
        
        // Create interval config
        $interval = ReminderIntervalConfig::create([
            'name' => 'One Week Before',
            'reminder_type' => 'pre_expiration',
            'days' => 7,
            'is_active' => true,
        ]);
        
        // Create reminder configuration for Type X only
        ReminderConfiguration::create([
            'name' => $interval->name,
            'reminder_type' => 'pre_expiration',
            'days_before_expiration' => $interval->days,
            'order_type_codes' => json_encode(['TYPE_X']), // Only for Type X
            'is_active' => true,
        ]);
        
        // Create business 
        $business = Business::create([
            'name' => 'Test Business',
            'contact_email' => 'test@example.com',
            'active' => true,
        ]);
        
        $expirationDate = Carbon::parse('2024-12-31');
        
        // Create Type X order
        $orderX = Order::create([
            'business_id' => $business->id,
            'order_type_id' => $typeX->id,
            'customer_name' => 'Type X Customer',
            'customer_email' => 'customer@example.com',
            'application_date' => Carbon::now(),
            'expiration_date' => $expirationDate,
            'status' => 'active',
        ]);
        
        // Create Type Y order
        $orderY = Order::create([
            'business_id' => $business->id,
            'order_type_id' => $typeY->id,
            'customer_name' => 'Type Y Customer',
            'customer_email' => 'customer@example.com',
            'application_date' => Carbon::now(),
            'expiration_date' => $expirationDate,
            'status' => 'active',
        ]);
        
        // Get dates for Type X order
        $datesX = $this->service->calculateReminderDates($orderX);
        
        // Get dates for Type Y order
        $datesY = $this->service->calculateReminderDates($orderY);
        
        // Type X should have dates
        $this->assertNotEmpty($datesX['pre_expiration']);
        
        // Type Y should not have dates
        $this->assertEmpty($datesY['pre_expiration']);
    }
} 