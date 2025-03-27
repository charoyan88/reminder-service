<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Order;
use App\Models\OrderType;
use App\Models\Reminder;
use App\Models\ReminderConfiguration;
use App\Models\ReminderIntervalConfig;
use App\Services\ReminderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReminderSchedulingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $reminderService;
    protected $business;
    protected $orderType;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create basic test data
        $this->business = Business::create([
            'name' => 'Test Business',
            'contact_email' => 'test@example.com',
            'active' => true
        ]);
        
        // Create order types
        $this->orderType = OrderType::create([
            'name' => 'Type X',
            'code' => 'TYPE_X',
            'description' => 'Expires 1 year after the application date',
            'expiration_type' => 'fixed_period',
            'expiration_period_months' => 12,
            'requires_renewal' => true,
            'allows_early_renewal' => true,
            'is_active' => true,
        ]);
        
        // Seed reminder intervals
        $this->seed(\Database\Seeders\ReminderIntervalConfigSeeder::class);
        
        // Initialize the reminder service
        $this->reminderService = app(ReminderService::class);
    }
    
    /** @test */
    public function it_schedules_reminders_based_on_intervals()
    {
        // Create an order with a known expiration date
        $expirationDate = Carbon::now()->addMonth();
        $order = Order::create([
            'business_id' => $this->business->id,
            'order_type_id' => $this->orderType->id,
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'application_date' => Carbon::now()->subDay(),
            'expiration_date' => $expirationDate,
            'status' => 'active'
        ]);
        
        // Get the reminder interval configurations
        $intervals = ReminderIntervalConfig::where('reminder_type', 'pre_expiration')
            ->where('is_active', true)
            ->get();
        
        // Create reminder configurations based on interval configs
        foreach ($intervals as $interval) {
            ReminderConfiguration::create([
                'name' => $interval->name,
                'reminder_type' => 'pre_expiration',
                'days_before_expiration' => $interval->days,
                'order_type_codes' => json_encode(['TYPE_X']),
                'is_active' => true
            ]);
        }
        
        // Call the reminder service to schedule reminders
        $this->reminderService->scheduleRemindersForOrder($order);
        
        // Verify reminders were created with the correct dates
        foreach ($intervals as $interval) {
            $expectedDate = $expirationDate->copy()->subDays($interval->days);
            
            $this->assertDatabaseHas('reminders', [
                'order_id' => $order->id,
                'reminder_type' => 'pre_expiration',
                'scheduled_date' => $expectedDate->format('Y-m-d')
            ]);
        }
    }
    
    /** @test */
    public function it_uses_only_active_intervals_for_scheduling()
    {
        // Disable one interval
        $oneWeekInterval = ReminderIntervalConfig::where('name', 'One Week Before')->first();
        $oneWeekInterval->update(['is_active' => false]);
        
        // Create the ReminderConfiguration objects
        $intervals = ReminderIntervalConfig::where('reminder_type', 'pre_expiration')
            ->where('is_active', true)
            ->get();
            
        foreach ($intervals as $interval) {
            ReminderConfiguration::create([
                'name' => $interval->name,
                'reminder_type' => 'pre_expiration',
                'days_before_expiration' => $interval->days,
                'order_type_codes' => json_encode(['TYPE_X']),
                'is_active' => true
            ]);
        }
        
        // Create an order
        $expirationDate = Carbon::now()->addMonth();
        $order = Order::create([
            'business_id' => $this->business->id,
            'order_type_id' => $this->orderType->id,
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'application_date' => Carbon::now()->subDay(),
            'expiration_date' => $expirationDate,
            'status' => 'active'
        ]);
        
        // Schedule reminders
        $this->reminderService->scheduleRemindersForOrder($order);
        
        // Verify the disabled interval didn't create a reminder
        $oneWeekBeforeDate = $expirationDate->copy()->subDays($oneWeekInterval->days);
        $this->assertDatabaseMissing('reminders', [
            'order_id' => $order->id,
            'scheduled_date' => $oneWeekBeforeDate->format('Y-m-d')
        ]);
        
        // But other active intervals did create reminders
        $threeDaysInterval = ReminderIntervalConfig::where('name', 'Three Days Before')->first();
        $threeDaysBeforeDate = $expirationDate->copy()->subDays($threeDaysInterval->days);
        $this->assertDatabaseHas('reminders', [
            'order_id' => $order->id,
            'scheduled_date' => $threeDaysBeforeDate->format('Y-m-d')
        ]);
    }
    
    /** @test */
    public function it_respects_order_type_filtering()
    {
        // Create a Type Y order type
        $typeY = OrderType::create([
            'name' => 'Type Y',
            'code' => 'TYPE_Y',
            'description' => 'Expires on December 31 of the current year',
            'expiration_type' => 'year_end',
            'requires_renewal' => true,
            'is_active' => true,
        ]);
        
        // Create reminder configurations for Type X only
        $intervals = ReminderIntervalConfig::where('reminder_type', 'pre_expiration')
            ->where('is_active', true)
            ->get();
            
        foreach ($intervals as $interval) {
            ReminderConfiguration::create([
                'name' => $interval->name,
                'reminder_type' => 'pre_expiration',
                'days_before_expiration' => $interval->days,
                'order_type_codes' => json_encode(['TYPE_X']), // Only for Type X
                'is_active' => true
            ]);
        }
        
        // Create a Type Y order
        $expirationDate = Carbon::now()->addMonth();
        $typeYOrder = Order::create([
            'business_id' => $this->business->id,
            'order_type_id' => $typeY->id,
            'customer_name' => 'Type Y Customer',
            'customer_email' => 'customer@example.com',
            'application_date' => Carbon::now()->subDay(),
            'expiration_date' => $expirationDate,
            'status' => 'active'
        ]);
        
        // Schedule reminders
        $this->reminderService->scheduleRemindersForOrder($typeYOrder);
        
        // Verify no reminders were created for Type Y
        $this->assertDatabaseMissing('reminders', [
            'order_id' => $typeYOrder->id
        ]);
        
        // Now create a Type X order
        $typeXOrder = Order::create([
            'business_id' => $this->business->id,
            'order_type_id' => $this->orderType->id, // Type X
            'customer_name' => 'Type X Customer',
            'customer_email' => 'customer@example.com',
            'application_date' => Carbon::now()->subDay(),
            'expiration_date' => $expirationDate,
            'status' => 'active'
        ]);
        
        // Schedule reminders
        $this->reminderService->scheduleRemindersForOrder($typeXOrder);
        
        // Verify reminders were created for Type X
        $this->assertDatabaseHas('reminders', [
            'order_id' => $typeXOrder->id
        ]);
    }
    
    /** @test */
    public function it_can_add_new_interval_and_use_it_for_scheduling()
    {
        // Create a new custom interval
        $customInterval = ReminderIntervalConfig::create([
            'name' => 'Two Weeks Before',
            'description' => 'Custom interval added for testing',
            'reminder_type' => 'pre_expiration',
            'days' => 14,
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 0
        ]);
        
        // Create configuration based on the new interval
        ReminderConfiguration::create([
            'name' => $customInterval->name,
            'reminder_type' => 'pre_expiration',
            'days_before_expiration' => $customInterval->days,
            'order_type_codes' => json_encode(['TYPE_X']),
            'is_active' => true
        ]);
        
        // Create an order
        $expirationDate = Carbon::now()->addMonth();
        $order = Order::create([
            'business_id' => $this->business->id,
            'order_type_id' => $this->orderType->id,
            'customer_name' => 'Test Customer',
            'customer_email' => 'customer@example.com',
            'application_date' => Carbon::now()->subDay(),
            'expiration_date' => $expirationDate,
            'status' => 'active'
        ]);
        
        // Schedule reminders
        $this->reminderService->scheduleRemindersForOrder($order);
        
        // Verify reminder for the new interval was created
        $customIntervalDate = $expirationDate->copy()->subDays($customInterval->days);
        $this->assertDatabaseHas('reminders', [
            'order_id' => $order->id,
            'scheduled_date' => $customIntervalDate->format('Y-m-d')
        ]);
    }
} 