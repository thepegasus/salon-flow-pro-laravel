<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\ActsAsTenant;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use ActsAsTenant, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpTenant();
    }

    public function test_user_with_valid_credentials_can_log_in(): void
    {
        $user = User::factory()->for($this->tenant)->create([
            'username' => 'priya',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postToTenant('/login', [
            'username' => 'priya',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_disabled_user_cannot_log_in_even_with_correct_password(): void
    {
        User::factory()->for($this->tenant)->create([
            'username' => 'priya',
            'password' => Hash::make('password123'),
            'disabled_at' => now(),
        ]);

        $response = $this->postToTenant('/login', [
            'username' => 'priya',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }
}
