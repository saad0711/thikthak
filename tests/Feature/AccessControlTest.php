<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_sent_to_login_for_account_pages(): void
    {
        $this->get('/account')->assertRedirect(route('login'));
    }

    public function test_customer_cannot_open_the_provider_dashboard(): void
    {
        $this->actingAs(User::factory()->customer()->create())
            ->get('/provider/dashboard')
            ->assertForbidden();
    }

    public function test_customer_cannot_open_admin_pages(): void
    {
        $this->actingAs(User::factory()->customer()->create())
            ->get('/admin/categories')
            ->assertForbidden();
    }

    public function test_provider_cannot_open_admin_pages(): void
    {
        $this->actingAs(User::factory()->provider()->create())
            ->get('/admin/dashboard')
            ->assertForbidden();
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function roles(): array
    {
        return [
            'customer' => ['customer', 'customer.dashboard'],
            'provider' => ['provider', 'provider.dashboard'],
            'admin' => ['admin', 'admin.dashboard'],
        ];
    }

    #[DataProvider('roles')]
    public function test_each_role_reaches_its_own_dashboard(string $role, string $dashboardRoute): void
    {
        $user = User::factory()->{$role}()->create();

        $this->actingAs($user)->get('/dashboard')->assertRedirect(route($dashboardRoute));
        $this->actingAs($user)->get(route($dashboardRoute))->assertOk();
    }

    public function test_logged_in_user_visiting_login_goes_to_their_dashboard(): void
    {
        $this->actingAs(User::factory()->create())->get('/login')->assertRedirect(route('dashboard'));
    }

    public function test_deactivated_user_with_an_open_session_is_logged_out(): void
    {
        $user = User::factory()->inactive()->create();

        $this->actingAs($user)->get('/customer/dashboard')->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
