<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_update_name_and_phone(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/profile', ['name' => 'New Name', 'phone' => '0300 1234567'])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New Name', 'phone' => '03001234567']);
    }

    public function test_invalid_phone_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put('/profile', ['name' => 'Ali', 'phone' => '12345'])
            ->assertSessionHasErrors('phone');
    }

    public function test_user_can_change_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'password',
            'password' => 'NewPass#2026x',
            'password_confirmation' => 'NewPass#2026x',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('NewPass#2026x', $user->fresh()->password));
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/profile/password', [
            'current_password' => 'wrong',
            'password' => 'NewPass#2026x',
            'password_confirmation' => 'NewPass#2026x',
        ])->assertSessionHasErrors('current_password', errorBag: 'password');
    }
}