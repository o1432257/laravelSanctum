<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminTest extends TestCase
{
    Use RefreshDatabase;

    protected function login()
    {
        Admin::factory()->state([
            'email' => 'admin@gmail.com',
            'password' => '$2y$10$panHCNblWiBsh8DwzyDIOevG0F3q5zEaRIGQFEQhHydE57SF/HDau'
        ])->create();

        $response = $this->json('POST', '/api/admin/login', [
            'email' => 'admin@gmail.com',
            'password' => 'password'
        ]);

        return $response;
    }

    public function testLogin()
    {
        $response = $this->login();

        $response->assertStatus(200);
    }

    /**
     *
     */
    public function testMemberInfo()
    {
        $response = $this->login();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . json_decode($response->getContent())->{'token'},
            'Accept' => 'application/json'
        ])->get('/api/admin/memberInfo');

        $response->assertStatus(200)->assertJson([
            'auth' => [
                'email' => 'admin@gmail.com'
            ]
        ]);
    }

    public function testLogout()
    {
        $response = $this->login();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . json_decode($response->getContent())->{'token'},
            'Accept' => 'application/json'
        ])->post('/api/admin/logout');

        $response->assertStatus(200)->assertJson([
            'message' => 'Token deleted successfully'
        ]);
    }

}
