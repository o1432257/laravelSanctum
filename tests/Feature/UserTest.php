<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class UserTest extends TestCase
{
    Use RefreshDatabase;

    protected function login()
    {
        User::factory()->state([
            'email' => 'user@gmail.com',
            'password' => '$2y$10$panHCNblWiBsh8DwzyDIOevG0F3q5zEaRIGQFEQhHydE57SF/HDau'
        ])->create();

        $captchaResponse = $this->get('/captcha/api/flat');

        $key = json_decode($captchaResponse->getContent())->{'key'};
        $value = implode(Cache::get('captcha_record_' . $key));

        $response = $this->json('POST', '/api/user/login', [
            'email' => 'user@gmail.com',
            'password' => 'password',
            'captcha' => $value,
            'key' => $key
        ]);

        return $response;
    }

    public function testLogin()
    {
        $response = $this->login();

        $response->assertStatus(200);
    }

    public function testMemberInfo()
    {
        $response = $this->login();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . json_decode($response->getContent())->{'token'},
            'Accept' => 'application/json'
        ])->get('/api/user/memberInfo');

        $response->assertStatus(200)->assertJson([
            'auth' => [
                'email' => 'user@gmail.com'
            ]
        ]);
    }

    public function testLogout()
    {
        $response = $this->login();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . json_decode($response->getContent())->{'token'},
            'Accept' => 'application/json'
        ])->post('/api/user/logout');

        $response->assertStatus(200)->assertJson([
            'message' => 'Token deleted successfully'
        ]);
    }
}
