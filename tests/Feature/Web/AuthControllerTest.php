<?php

namespace Tests\Feature\Web;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_form_is_shown_for_guest(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertViewIs('auth.login');
    }

    public function test_login_success_redirects_to_dashboard(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Admin,
            'code' => 'ADM100',
            'email' => 'admin@cca.test',
            'password' => 'password',
        ]);

        $this->from('/login')
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fail_returns_with_errors(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Admin,
            'code' => 'ADM101',
            'email' => 'admin.fail@cca.test',
            'password' => 'password',
        ]);

        $this->from('/login')
            ->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_logout_invalidates_session(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Admin,
            'code' => 'ADM102',
        ]);

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_guest_is_redirected_from_dashboard_to_login(): void
    {
        $this->get('/dashboard')
            ->assertRedirect(route('login'));
    }
}
