<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function formData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Karim Ahmed',
            'email' => 'karim@example.com',
            'phone' => '01712345678',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'role' => 'customer',
            'area' => 'Dhanmondi',
        ], $overrides);
    }

    public function test_registration_page_loads(): void
    {
        $this->get('/register')->assertOk()->assertSee('Create your ThikThak account');
    }

    public function test_customer_can_register_and_is_asked_to_verify_their_email(): void
    {
        Notification::fake();

        $this->post('/register', $this->formData())->assertRedirect(route('customer.dashboard'));

        $user = User::where('email', 'karim@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame(UserRole::Customer, $user->role);
        $this->assertNull($user->providerProfile);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_provider_registration_creates_an_empty_provider_profile(): void
    {
        $this->post('/register', $this->formData(['role' => 'provider']))->assertRedirect(route('provider.dashboard'));

        $user = User::where('email', 'karim@example.com')->firstOrFail();
        $this->assertTrue($user->isProvider());
        $this->assertNotNull($user->providerProfile);
    }

    public function test_nobody_can_sign_up_as_an_admin(): void
    {
        $this->post('/register', $this->formData(['role' => 'admin']))->assertSessionHasErrors('role');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_email_must_not_already_be_registered(): void
    {
        User::factory()->create(['email' => 'karim@example.com']);

        $this->post('/register', $this->formData())->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_phone_must_be_a_bangladeshi_mobile_number(): void
    {
        $this->post('/register', $this->formData(['phone' => '0123456789']))->assertSessionHasErrors('phone');

        $this->assertGuest();
    }

    public function test_password_needs_at_least_8_characters(): void
    {
        $this->post('/register', $this->formData(['password' => 'secret1', 'password_confirmation' => 'secret1']))
            ->assertSessionHasErrors('password');
        $this->assertGuest();

        $this->post('/register', $this->formData(['password' => 'secret12', 'password_confirmation' => 'secret12']))
            ->assertSessionHasNoErrors();
        $this->assertAuthenticated();
    }

    public function test_password_is_stored_hashed(): void
    {
        $this->post('/register', $this->formData());

        $user = User::where('email', 'karim@example.com')->firstOrFail();
        $this->assertNotSame('secret123', $user->password);
        $this->assertTrue(Hash::check('secret123', $user->password));
    }
}
