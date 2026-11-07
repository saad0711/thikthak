<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AccountSettingsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function details(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Rahima Begum',
            'phone' => '01812345678',
            'area' => 'Uttara',
            'address' => 'House 12, Road 5, Sector 7',
        ], $overrides);
    }

    public function test_account_page_loads(): void
    {
        $this->actingAs(User::factory()->create())->get('/account')->assertOk()->assertSee('Account settings');
    }

    public function test_user_can_update_their_details(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/account', $this->details())->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('Rahima Begum', $user->name);
        $this->assertSame('01812345678', $user->phone);
        $this->assertSame('Uttara', $user->area);
    }

    public function test_user_can_upload_a_profile_photo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->put('/account', $this->details([
            'avatar' => UploadedFile::fake()->image('me.jpg', 300, 300)->size(500),
        ]))->assertSessionHasNoErrors();

        $this->assertNotNull($user->fresh()->avatar_path);
        Storage::disk('public')->assertExists($user->fresh()->avatar_path);
    }

    public function test_profile_photo_must_be_an_image_of_at_most_2_mb(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)->put('/account', $this->details([
            'avatar' => UploadedFile::fake()->create('cv.pdf', 5000, 'application/pdf'),
        ]))->assertSessionHasErrors('avatar');

        $this->actingAs($user)->put('/account', $this->details([
            'avatar' => UploadedFile::fake()->image('huge.jpg')->size(3000),
        ]))->assertSessionHasErrors('avatar');

        $this->assertNull($user->fresh()->avatar_path);
    }

    public function test_phone_already_used_by_someone_else_is_rejected(): void
    {
        User::factory()->create(['phone' => '01812345678']);
        $user = User::factory()->create();

        $this->actingAs($user)->put('/account', $this->details())->assertSessionHasErrors('phone');
    }

    public function test_changing_password_needs_the_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/account/password', [
            'current_password' => 'wrong-password',
            'password' => 'brand-new-pass',
            'password_confirmation' => 'brand-new-pass',
        ])->assertSessionHasErrorsIn('password', 'current_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_user_can_change_their_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put('/account/password', [
            'current_password' => 'password',
            'password' => 'brand-new-pass',
            'password_confirmation' => 'brand-new-pass',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('brand-new-pass', $user->fresh()->password));
    }

    public function test_user_can_deactivate_their_account(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/account/deactivate', ['password' => 'password'])->assertRedirect(route('home'));

        $this->assertGuest();
        $this->assertFalse($user->fresh()->is_active);
    }

    public function test_deactivating_needs_the_correct_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/account/deactivate', ['password' => 'wrong'])
            ->assertSessionHasErrorsIn('deactivate', 'password');

        $this->assertTrue($user->fresh()->is_active);
    }

    public function test_admin_cannot_deactivate_their_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/account/deactivate', ['password' => 'password'])->assertForbidden();

        $this->assertTrue($admin->fresh()->is_active);
    }
}
