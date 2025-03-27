<?php

namespace Tests\Feature;

use App\Models\ReminderIntervalConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ReminderIntervalConfigTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed the database with default intervals
        $this->seed(\Database\Seeders\ReminderIntervalConfigSeeder::class);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_list_all_reminder_intervals()
    {
        $response = $this->getJson('/api/reminder-intervals');
        
        $response->assertStatus(200);
        // Check the response structure
        $response->assertJsonStructure([
            'message',
            'data'
        ]);
        
        // Assert that we have 4 default intervals from the seeder
        $this->assertCount(4, $response->json('data'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_a_new_reminder_interval()
    {
        $data = [
            'name' => 'Two Weeks Before',
            'description' => 'Send reminder two weeks before expiration',
            'reminder_type' => 'pre_expiration',
            'days' => 14,
            'is_default' => false,
            'is_active' => true,
            'sort_order' => 0
        ];
        
        $response = $this->postJson('/api/reminder-intervals', $data);
        
        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Successfully created a new reminder interval',
                'data' => [
                    'name' => 'Two Weeks Before',
                    'days' => 14,
                    'reminder_type' => 'pre_expiration'
                ]
            ]);
            
        $this->assertDatabaseHas('reminder_interval_configs', [
            'name' => 'Two Weeks Before',
            'days' => 14
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_required_fields_when_creating()
    {
        $response = $this->postJson('/api/reminder-intervals', []);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'reminder_type', 'days']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_prevents_duplicate_intervals_with_same_type_and_days()
    {
        // Create first interval
        $this->postJson('/api/reminder-intervals', [
            'name' => 'Two Weeks Before',
            'reminder_type' => 'pre_expiration',
            'days' => 14,
            'is_active' => true
        ]);
        
        // Attempt to create duplicate with same type and days
        $response = $this->postJson('/api/reminder-intervals', [
            'name' => 'Another Two Weeks Before',
            'reminder_type' => 'pre_expiration',
            'days' => 14,
            'is_active' => true
        ]);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['days']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_allows_same_days_with_different_reminder_types()
    {
        // Create pre-expiration interval
        $this->postJson('/api/reminder-intervals', [
            'name' => 'Two Weeks Before',
            'reminder_type' => 'pre_expiration',
            'days' => 14,
            'is_active' => true
        ]);
        
        // Create post-expiration interval with same days value
        $response = $this->postJson('/api/reminder-intervals', [
            'name' => 'Two Weeks After',
            'reminder_type' => 'post_expiration',
            'days' => 14,
            'is_active' => true
        ]);
        
        $response->assertStatus(201);
            
        $this->assertDatabaseHas('reminder_interval_configs', [
            'name' => 'Two Weeks After',
            'reminder_type' => 'post_expiration',
            'days' => 14
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_update_an_existing_interval()
    {
        // Create an interval
        $interval = ReminderIntervalConfig::create([
            'name' => 'Test Interval',
            'description' => 'Original description',
            'reminder_type' => 'pre_expiration',
            'days' => 5,
            'is_active' => true,
            'sort_order' => 0
        ]);
        
        $response = $this->putJson("/api/reminder-intervals/{$interval->id}", [
            'name' => 'Updated Interval',
            'description' => 'Updated description'
        ]);
        
        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully updated the reminder interval',
                'data' => [
                    'name' => 'Updated Interval',
                    'description' => 'Updated description',
                    'days' => 5 // Original value preserved
                ]
            ]);
            
        $this->assertDatabaseHas('reminder_interval_configs', [
            'id' => $interval->id,
            'name' => 'Updated Interval',
            'description' => 'Updated description'
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_prevents_update_to_duplicate_type_and_days()
    {
        // Create two intervals
        $interval1 = ReminderIntervalConfig::create([
            'name' => 'First Interval',
            'reminder_type' => 'pre_expiration',
            'days' => 5,
            'is_active' => true
        ]);
        
        $interval2 = ReminderIntervalConfig::create([
            'name' => 'Second Interval',
            'reminder_type' => 'pre_expiration',
            'days' => 10,
            'is_active' => true
        ]);
        
        // Try to update second interval to same days as first
        $response = $this->putJson("/api/reminder-intervals/{$interval2->id}", [
            'days' => 5
        ]);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['days']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_toggle_interval_status()
    {
        $interval = ReminderIntervalConfig::where('name', 'One Week Before')->first();
        $originalStatus = $interval->is_active;
        
        $response = $this->postJson("/api/reminder-intervals/{$interval->id}/toggle-status");
        
        $response->assertStatus(200)
            ->assertJson([
                'message' => $originalStatus 
                    ? 'The reminder interval has been deactivated' 
                    : 'The reminder interval has been activated',
                'data' => [
                    'is_active' => !$originalStatus
                ]
            ]);
            
        $this->assertDatabaseHas('reminder_interval_configs', [
            'id' => $interval->id,
            'is_active' => !$originalStatus
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_delete_non_default_interval()
    {
        $interval = ReminderIntervalConfig::create([
            'name' => 'Temporary Interval',
            'reminder_type' => 'pre_expiration',
            'days' => 12,
            'is_default' => false,
            'is_active' => true
        ]);
        
        $response = $this->deleteJson("/api/reminder-intervals/{$interval->id}");
        
        $response->assertStatus(204);
        
        $this->assertSoftDeleted('reminder_interval_configs', [
            'id' => $interval->id
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_cannot_delete_default_interval()
    {
        $interval = ReminderIntervalConfig::where('is_default', true)->first();
        
        $response = $this->deleteJson("/api/reminder-intervals/{$interval->id}");
        
        $response->assertStatus(422);
        
        $this->assertDatabaseHas('reminder_interval_configs', [
            'id' => $interval->id,
            'deleted_at' => null
        ]);
    }
} 