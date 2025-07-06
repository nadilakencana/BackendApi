<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class ChatApiTest extends TestCase
{
    /**
     * A basic feature test example.
     */


    use RefreshDatabase, WithFaker;


   public function test_authenticated_user_can_send_chat_message()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user); // Login sebagai user ini untuk test

        $response = $this->postJson('/api/chat', [
            'message' => 'Hello AI, how are you?',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['id_session', 'reply']);

        $this->assertDatabaseHas('session_chats', [
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('history_chats', [
            'message' => 'Hello AI, how are you?',
            'sender_type' => 'user',
        ]);
    }

    public function test_unauthenticated_user_cannot_send_chat_message()
    {
        $response = $this->postJson('/api/chat', [
            'message' => 'Hello AI, how are you?',
        ]);

        $response->assertStatus(401); // Unauthorized
    }

    public function test_authenticated_user_can_get_chat_sessions()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Buat beberapa sesi untuk user ini
        $session1 = $user->sessions()->create(['id' => (string) \Illuminate\Support\Str::uuid(), 'title' => 'Session A']);
        $session2 = $user->sessions()->create(['id' => (string) \Illuminate\Support\Str::uuid(), 'title' => 'Session B']);

        $response = $this->getJson('/api/sessions');

        $response->assertStatus(200)
                 ->assertJsonCount(2) // Pastikan hanya ada 2 sesi
                 ->assertJsonFragment(['id' => $session1->id])
                 ->assertJsonFragment(['id' => $session2->id]);
    }

     public function test_authenticated_user_can_get_specific_session_history()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $session = $user->sessions()->create(['id' => (string) \Illuminate\Support\Str::uuid()]);
        $session->messages()->create(['sender_type' => 'user', 'message' => 'Msg 1']);
        $session->messages()->create(['sender_type' => 'ai', 'message' => 'Reply 1']);

        $response = $this->getJson("/api/sessions/{$session->id}/history");

        $response->assertStatus(200)
                 ->assertJsonCount(2)
                 ->assertJsonFragment(['message' => 'Msg 1'])
                 ->assertJsonFragment(['message' => 'Reply 1']);
    }
     public function test_authenticated_user_cannot_get_other_users_session_history()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Sanctum::actingAs($user1); // Login sebagai user1

        $session2 = $user2->sessions()->create(['id' => (string) \Illuminate\Support\Str::uuid()]); // Sesi milik user2
        $session2->messages()->create(['sender_type' => 'user', 'message' => 'Secret Msg']);

        $response = $this->getJson("/api/sessions/{$session2->id}/history");

        $response->assertStatus(404); // Seharusnya 404 karena sesi tidak ditemukan untuk user1
    }
}
