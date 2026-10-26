<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_signin(): void
    {
        $this->get('/')->assertRedirect('/signin');
    }

    public function test_user_can_login_with_correct_password(): void
    {
        $user = User::factory()->create();

        $this->post('/signin', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_fails(): void
    {
        $user = User::factory()->create();

        $this->post('/signin', ['email' => $user->email, 'password' => 'wrong'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $this->post('/signin', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect(route('signin'));

        $this->assertGuest();
    }

    public function test_register_is_closed_when_a_user_exists(): void
    {
        User::factory()->create();

        $this->get('/register')->assertRedirect(route('signin'));
        $this->post('/register', [])->assertForbidden();
    }
}