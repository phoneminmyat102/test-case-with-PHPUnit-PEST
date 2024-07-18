<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{

    use RefreshDatabase;
    public function test_loginSuccess_redirect_to_product_page()
    {
        User::create([
            'name' => 'User One',
            'email' => 'userone@gmail.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->post('login', [
            'email' => 'userone@gmail.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/products/all');
    }
    public function test_unauthenticated_user_cannot_access_product_page(): void
    {
        $response = $this->get('/products/all');

        $response->assertStatus(302);
        $response->assertRedirect('login');
    }

}
