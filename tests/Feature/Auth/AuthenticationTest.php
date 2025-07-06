<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

use function Illuminate\Log\log;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        // $this->assertAuthenticated();
        // $response->dump();
         $response->assertStatus(200)
                 ->assertJsonStructure(['user', 'token']);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user); 

        $user->createToken('auth_token')->plainTextToken; 

        $response = $this->postJson('/api/logout'); 

     
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Logged out']); 

        
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            
        ]);
    }
}
