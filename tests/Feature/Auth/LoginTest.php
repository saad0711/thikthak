<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_loads(): void
    {
        $this->get('/login')->assertOk()->assertSee('Log in to ThikThak');
    }

    public function test_customer_logs_in_and_lands_on_their_dashboard(): void
    {
        $user = User::factory()->customer()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('customer.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_lands_on_the_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_wrong_password_shows_a_generic_error(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors(['email' => __('auth.failed')]);

        $this->assertGuest();
    }

    public function test_unknown_email_shows_the_same_generic_error(): void
    {
        $this->post('/login', ['email' => 'nobody@example.com', 'password' => 'password'])
            ->assertSessionHasErrors(['email' => __('auth.failed')]);
    }

    public function test_sixth_login_attempt_in_a_minute_is_throttled(): void
    {
        $user = User::factory()->create();

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertRedirect();
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertStatus(429);
    }

    public function test_deactivated_account_cannot_log_in(): void
    {
        $user = User::factory()->inactive()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => 'This account has been deactivated.']);

        $this->assertGuest();
    }

    public function test_remember_me_sets_the_remember_cookie(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password', 'remember' => '1'])
            ->assertCookie(Auth::guard()->getRecallerName());
    }

    public function test_user_can_log_out(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect(route('home'));

        $this->assertGuest();
    }
}
