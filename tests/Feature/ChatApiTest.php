<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_can_sign_up_and_read_their_profile(): void
    {
        $response = $this->postJson('/api/signup', [
            'full_name' => 'New User',
            'email' => 'new@example.com',
            'username' => 'newuser',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.username', 'newuser');

        $this->getJson('/api/profile')
            ->assertOk()
            ->assertJsonPath('data.email', 'new@example.com');
    }

    public function test_users_can_send_and_read_messages(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        $this->withSession([
            'loggedin' => true,
            'user_id' => $sender->id,
        ])->postJson('/api/send-message', [
            'receiver_id' => $receiver->id,
            'content' => 'Hello',
        ])->assertCreated()
            ->assertJsonPath('data.content', 'Hello');

        $this->withSession([
            'loggedin' => true,
            'user_id' => $receiver->id,
        ])->postJson('/api/messages', [
            'id' => $sender->id,
        ])->assertOk()
            ->assertJsonPath('data.0.content', 'Hello');
    }

    public function test_protected_endpoints_reject_anonymous_requests(): void
    {
        $this->getJson('/api/profile')->assertUnauthorized();
        $this->getJson('/api/users')->assertUnauthorized();
        $this->getJson('/api/contacts')->assertUnauthorized();
    }
}
