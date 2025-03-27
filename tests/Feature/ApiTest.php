<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_access_api_status()
    {
        $response = $this->getJson('/api/status');
        
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
                'service' => 'reminder-service'
            ]);
    }
    
    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_access_web_api_status()
    {
        $response = $this->get('/api/status');
        
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'ok',
                'service' => 'reminder-service'
            ]);
    }
} 