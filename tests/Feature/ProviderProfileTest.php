<?php

namespace Tests\Feature;

use App\Models\Certification;
use App\Models\PortfolioItem;
use App\Models\ProviderProfile;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

// Tests for F13 (Professional Profile & Portfolio).
// Each test has a plain-English name. Groups: access, basic info, services,
// service area, certifications, portfolio, completeness score.
class ProviderProfileTest extends TestCase
{
    // RefreshDatabase: every test starts with an empty, temporary database.
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Uploaded files go to a fake disk, so no real files are created.
        Storage::fake('public');
        Storage::fake('local');

        // The tests do not need the compiled CSS/JS, so skip the Vite files.
        $this->withoutVite();
    }

    // =====================================================================
    // Helpers (small functions the tests reuse)
    // =====================================================================

    private function makeProvider(): User
    {
        return User::factory()->create(['role' => 'provider']);
    }

    private function makeCustomer(): User
    {
        return User::factory()->create(['role' => 'customer']);
    }

    private function makeProfile(User $user, array $extra = []): ProviderProfile
    {
        return ProviderProfile::create(array_merge(['user_id' => $user->id], $extra));
    }

    private function makeService(float $min = 500, float $max = 2000): Service
    {
        $category = ServiceCategory::query()->create([
            'name' => 'F13 Test Category',
            'icon' => '🛠️',
            'description' => 'Category used by F13 tests.',
            'is_active' => true,
            'sort_order' => 999,
        ]);

        return Service::query()->create([
            'service_category_id' => $category->id,
            'name' => 'F13 Test Service',
            'description' => 'Service used by F13 tests.',
            'price_min' => $min,
            'price_max' => $max,
            'is_active' => true,
            'sort_order' => 0,
        ]);
    }

    private function addPortfolioItems(ProviderProfile $profile, int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $profile->portfolioItems()->create(['image_path' => 'portfolio/fake-' . $i . '.jpg']);
        }
    }

    private function certificationData(array $extra = []): array
    {
        return array_merge([
            'title' => 'Certified Electrician',
            'issuer' => 'Dhaka Technical Institute',
            'year' => 2020,
        ], $extra);
    }

    private function areaData(array $extra = []): array
    {
        return array_merge([
            'area_name' => 'Mirpur, Dhaka',
            'latitude' => 23.8069,
            'longitude' => 90.3687,
            'radius_km' => 10,
        ], $extra);
    }

    // Send the "services" form with one ticked service and one price.
    private function saveService(User $provider, Service $service, $price)
    {
        return $this->actingAs($provider)->put(route('provider.services.update'), [
            'service_ids' => [$service->id],
            'prices' => [$service->id => $price],
        ]);
    }

    // =====================================================================
    // 1. Access rules
    // =====================================================================

    // (This test needs a route named "login", which Laravel's login page normally has.)
    public function test_guest_is_sent_to_login_when_opening_my_profile(): void
    {
        $this->get(route('provider.profile.edit'))->assertRedirect(route('login'));
    }

    public function test_customer_cannot_open_my_profile(): void
    {
        $this->actingAs($this->makeCustomer())
            ->get(route('provider.profile.edit'))
            ->assertForbidden();

        // A customer must not get a profile row by mistake.
        $this->assertDatabaseCount('provider_profiles', 0);
    }

    public function test_customer_cannot_save_profile_services_area_certification_or_portfolio(): void
    {
        $customer = $this->makeCustomer();
        $service = $this->makeService();

        $this->actingAs($customer)->put(route('provider.profile.update'), ['bio' => 'Hi'])->assertForbidden();
        $this->actingAs($customer)->put(route('provider.services.update'), [
            'service_ids' => [$service->id],
            'prices' => [$service->id => 1000],
        ])->assertForbidden();
        $this->actingAs($customer)->put(route('provider.area.update'), $this->areaData())->assertForbidden();
        $this->actingAs($customer)->post(route('provider.certifications.store'), $this->certificationData())->assertForbidden();
        $this->actingAs($customer)->post(route('provider.portfolio.store'), [
            'image' => UploadedFile::fake()->image('work.jpg'),
        ])->assertForbidden();

        $this->assertDatabaseCount('provider_profiles', 0);
    }

    public function test_provider_can_open_my_profile_and_a_profile_row_is_created(): void
    {
        $provider = $this->makeProvider();

        $this->actingAs($provider)
            ->get(route('provider.profile.edit'))
            ->assertOk()
            ->assertSee('My Profile');

        $this->assertDatabaseHas('provider_profiles', ['user_id' => $provider->id]);
    }

    public function test_opening_my_profile_twice_does_not_create_two_rows(): void
    {
        $provider = $this->makeProvider();

        $this->actingAs($provider)->get(route('provider.profile.edit'));
        $this->actingAs($provider)->get(route('provider.profile.edit'));

        $this->assertDatabaseCount('provider_profiles', 1);
    }

    public function test_guest_can_see_a_public_profile(): void
    {
        $user = User::factory()->create(['role' => 'provider', 'name' => 'Rahim Uddin']);
        $profile = $this->makeProfile($user, ['bio' => 'I fix pipes.']);

        $this->get(route('providers.show', $profile))
            ->assertOk()
            ->assertSee('Rahim Uddin')
            ->assertSee('I fix pipes.');
    }

    public function test_public_profile_does_not_show_contact_details(): void
    {
        $user = User::factory()->create(['role' => 'provider', 'email' => 'secret.provider@example.com']);
        $profile = $this->makeProfile($user);

        $this->get(route('providers.show', $profile))
            ->assertOk()
            ->assertDontSee('secret.provider@example.com');
    }

    public function test_public_profile_does_not_show_the_exact_map_pin(): void
    {
        $user = $this->makeProvider();
        $profile = $this->makeProfile($user, $this->areaData(['latitude' => 23.8069000, 'longitude' => 90.3687000]));

        $this->get(route('providers.show', $profile))
            ->assertOk()
            ->assertSee('Mirpur, Dhaka')
            ->assertDontSee('23.8069')
            ->assertDontSee('90.3687');
    }

    public function test_public_profile_of_an_unknown_id_gives_404(): void
    {
        $this->get('/providers/9999')->assertNotFound();
    }

    // =====================================================================
    // 2. Basic information (bio, experience, photo)
    // =====================================================================

    public function test_provider_can_save_bio_and_experience(): void
    {
        $provider = $this->makeProvider();

        $this->actingAs($provider)->put(route('provider.profile.update'), [
            'bio' => 'I have fixed pipes for six years.',
            'years_experience' => 6,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('provider_profiles', [
            'user_id' => $provider->id,
            'bio' => 'I have fixed pipes for six years.',
            'years_experience' => 6,
        ]);
    }

    public function test_bio_of_exactly_1000_characters_is_accepted(): void
    {
        $this->actingAs($this->makeProvider())
            ->put(route('provider.profile.update'), ['bio' => str_repeat('a', 1000)])
            ->assertSessionHasNoErrors();
    }

    public function test_bio_of_1001_characters_is_rejected(): void
    {
        $this->actingAs($this->makeProvider())
            ->put(route('provider.profile.update'), ['bio' => str_repeat('a', 1001)])
            ->assertSessionHasErrors('bio');
    }

    public function test_experience_of_0_and_60_years_is_accepted(): void
    {
        $provider = $this->makeProvider();

        foreach ([0, 60] as $years) {
            $this->actingAs($provider)
                ->put(route('provider.profile.update'), ['years_experience' => $years])
                ->assertSessionHasNoErrors();
        }
    }

    public function test_experience_of_minus_1_and_61_years_is_rejected(): void
    {
        $provider = $this->makeProvider();

        foreach ([-1, 61] as $years) {
            $this->actingAs($provider)
                ->put(route('provider.profile.update'), ['years_experience' => $years])
                ->assertSessionHasErrors('years_experience');
        }
    }

    public function test_photo_of_exactly_2mb_is_accepted(): void
    {
        $this->actingAs($this->makeProvider())
            ->put(route('provider.profile.update'), ['photo' => UploadedFile::fake()->image('me.jpg')->size(2048)])
            ->assertSessionHasNoErrors();
    }

    public function test_photo_over_2mb_is_rejected(): void
    {
        $this->actingAs($this->makeProvider())
            ->put(route('provider.profile.update'), ['photo' => UploadedFile::fake()->image('me.jpg')->size(2049)])
            ->assertSessionHasErrors('photo');
    }

    public function test_photo_that_is_a_pdf_is_rejected(): void
    {
        $this->actingAs($this->makeProvider())
            ->put(route('provider.profile.update'), ['photo' => UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf')])
            ->assertSessionHasErrors('photo');
    }

    public function test_new_photo_is_stored_and_replaces_the_old_one(): void
    {
        $provider = $this->makeProvider();

        $this->actingAs($provider)->put(route('provider.profile.update'), ['photo' => UploadedFile::fake()->image('first.jpg')]);
        $oldPath = ProviderProfile::first()->photo_path;
        Storage::disk('public')->assertExists($oldPath);

        $this->actingAs($provider)->put(route('provider.profile.update'), ['photo' => UploadedFile::fake()->image('second.jpg')]);
        $newPath = ProviderProfile::first()->photo_path;

        Storage::disk('public')->assertExists($newPath);
        Storage::disk('public')->assertMissing($oldPath);
    }

    // =====================================================================
    // 3. Services at the provider's own prices (service range 500 to 2000)
    // =====================================================================

    public function test_provider_can_offer_a_service_at_a_price_inside_the_range(): void
    {
        $provider = $this->makeProvider();
        $service = $this->makeService(500, 2000);

        $this->saveService($provider, $service, 1000)->assertSessionHasNoErrors();

        $this->assertDatabaseHas('provider_service', ['service_id' => $service->id, 'price' => 1000]);
    }

    public function test_price_exactly_at_the_minimum_is_accepted(): void
    {
        $service = $this->makeService(500, 2000);

        $this->saveService($this->makeProvider(), $service, 500)->assertSessionHasNoErrors();
    }

    public function test_price_exactly_at_the_maximum_is_accepted(): void
    {
        $service = $this->makeService(500, 2000);

        $this->saveService($this->makeProvider(), $service, 2000)->assertSessionHasNoErrors();
    }

    public function test_price_below_the_minimum_is_rejected(): void
    {
        $service = $this->makeService(500, 2000);

        $this->saveService($this->makeProvider(), $service, 499.99)
            ->assertSessionHasErrors('prices.' . $service->id);

        $this->assertDatabaseCount('provider_service', 0);
    }

    public function test_price_above_the_maximum_is_rejected(): void
    {
        $service = $this->makeService(500, 2000);

        $this->saveService($this->makeProvider(), $service, 2000.01)
            ->assertSessionHasErrors('prices.' . $service->id);

        $this->assertDatabaseCount('provider_service', 0);
    }

    public function test_ticked_service_without_a_price_is_rejected(): void
    {
        $service = $this->makeService();

        $this->actingAs($this->makeProvider())
            ->put(route('provider.services.update'), ['service_ids' => [$service->id]])
            ->assertSessionHasErrors('prices.' . $service->id);
    }

    public function test_price_that_is_not_a_number_is_rejected(): void
    {
        $service = $this->makeService();

        $this->saveService($this->makeProvider(), $service, 'cheap')
            ->assertSessionHasErrors('prices.' . $service->id);
    }

    public function test_service_that_does_not_exist_is_rejected(): void
    {
        $this->actingAs($this->makeProvider())
            ->put(route('provider.services.update'), [
                'service_ids' => [9999],
                'prices' => [9999 => 1000],
            ])
            ->assertSessionHasErrors('service_ids.0');
    }

    public function test_changing_the_price_updates_the_same_row(): void
    {
        $provider = $this->makeProvider();
        $service = $this->makeService(500, 2000);

        $this->saveService($provider, $service, 1000);
        $this->saveService($provider, $service, 1500);

        $this->assertDatabaseCount('provider_service', 1);
        $this->assertDatabaseHas('provider_service', ['service_id' => $service->id, 'price' => 1500]);
    }

    public function test_unticking_all_services_removes_them(): void
    {
        $provider = $this->makeProvider();
        $service = $this->makeService();

        $this->saveService($provider, $service, 1000);
        $this->assertDatabaseCount('provider_service', 1);

        // Nothing ticked: the browser sends no service_ids at all.
        $this->actingAs($provider)->put(route('provider.services.update'), [])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('provider_service', 0);
    }

    // =====================================================================
    // 4. Service area
    // =====================================================================

    public function test_provider_can_save_a_valid_service_area(): void
    {
        $provider = $this->makeProvider();

        $this->actingAs($provider)
            ->put(route('provider.area.update'), $this->areaData())
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('provider_profiles', [
            'user_id' => $provider->id,
            'area_name' => 'Mirpur, Dhaka',
            'radius_km' => 10,
        ]);
    }

    public function test_radius_of_1_and_50_km_is_accepted(): void
    {
        $provider = $this->makeProvider();

        foreach ([1, 50] as $km) {
            $this->actingAs($provider)
                ->put(route('provider.area.update'), $this->areaData(['radius_km' => $km]))
                ->assertSessionHasNoErrors();
        }
    }

    public function test_radius_of_0_and_51_km_is_rejected(): void
    {
        $provider = $this->makeProvider();

        foreach ([0, 51] as $km) {
            $this->actingAs($provider)
                ->put(route('provider.area.update'), $this->areaData(['radius_km' => $km]))
                ->assertSessionHasErrors('radius_km');
        }
    }

    public function test_latitude_and_longitude_at_the_extreme_edges_are_accepted(): void
    {
        $provider = $this->makeProvider();

        $this->actingAs($provider)
            ->put(route('provider.area.update'), $this->areaData(['latitude' => 90, 'longitude' => 180]))
            ->assertSessionHasNoErrors();

        $this->actingAs($provider)
            ->put(route('provider.area.update'), $this->areaData(['latitude' => -90, 'longitude' => -180]))
            ->assertSessionHasNoErrors();
    }

    public function test_latitude_above_90_is_rejected(): void
    {
        $this->actingAs($this->makeProvider())
            ->put(route('provider.area.update'), $this->areaData(['latitude' => 91]))
            ->assertSessionHasErrors('latitude');
    }

    public function test_longitude_above_180_is_rejected(): void
    {
        $this->actingAs($this->makeProvider())
            ->put(route('provider.area.update'), $this->areaData(['longitude' => 181]))
            ->assertSessionHasErrors('longitude');
    }

    public function test_service_area_without_a_map_pin_is_rejected(): void
    {
        $this->actingAs($this->makeProvider())
            ->put(route('provider.area.update'), $this->areaData(['latitude' => null, 'longitude' => null]))
            ->assertSessionHasErrors(['latitude', 'longitude']);
    }

    public function test_service_area_without_a_name_is_rejected(): void
    {
        $this->actingAs($this->makeProvider())
            ->put(route('provider.area.update'), $this->areaData(['area_name' => '']))
            ->assertSessionHasErrors('area_name');
    }

    // =====================================================================
    // 5. Certifications
    // =====================================================================

    public function test_provider_can_add_a_certification_without_a_file(): void
    {
        $this->actingAs($this->makeProvider())
            ->post(route('provider.certifications.store'), $this->certificationData())
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('certifications', ['title' => 'Certified Electrician', 'year' => 2020]);
    }

    public function test_provider_can_add_a_certification_with_a_pdf_file(): void
    {
        $this->actingAs($this->makeProvider())
            ->post(route('provider.certifications.store'), $this->certificationData([
                'file' => UploadedFile::fake()->create('certificate.pdf', 200, 'application/pdf'),
            ]))
            ->assertSessionHasNoErrors();

        $certification = Certification::first();
        $this->assertNotNull($certification->file_path);
        Storage::disk('local')->assertExists($certification->file_path);
    }

    public function test_only_the_owner_can_download_a_private_certification_file(): void
    {
        $owner = $this->makeProvider();
        $this->actingAs($owner)->post(route('provider.certifications.store'), $this->certificationData([
            'file' => UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf'),
        ]));
        $certification = Certification::firstOrFail();

        $this->actingAs($owner)
            ->get(route('provider.certifications.file', $certification))
            ->assertOk();

        $otherProvider = $this->makeProvider();
        $this->actingAs($otherProvider)
            ->get(route('provider.certifications.file', $certification))
            ->assertForbidden();
    }

    public function test_provider_can_add_a_certification_with_an_image_file(): void
    {
        $this->actingAs($this->makeProvider())
            ->post(route('provider.certifications.store'), $this->certificationData([
                'file' => UploadedFile::fake()->image('certificate.png'),
            ]))
            ->assertSessionHasNoErrors();
    }

    public function test_title_issuer_and_year_are_required(): void
    {
        $this->actingAs($this->makeProvider())
            ->post(route('provider.certifications.store'), [])
            ->assertSessionHasErrors(['title', 'issuer', 'year']);
    }

    public function test_certification_year_1970_and_the_current_year_are_accepted(): void
    {
        $provider = $this->makeProvider();

        foreach ([1970, now()->year] as $year) {
            $this->actingAs($provider)
                ->post(route('provider.certifications.store'), $this->certificationData(['year' => $year]))
                ->assertSessionHasNoErrors();
        }
    }

    public function test_certification_year_1969_and_next_year_are_rejected(): void
    {
        $provider = $this->makeProvider();

        foreach ([1969, now()->year + 1] as $year) {
            $this->actingAs($provider)
                ->post(route('provider.certifications.store'), $this->certificationData(['year' => $year]))
                ->assertSessionHasErrors('year');
        }
    }

    public function test_certification_file_of_exactly_2mb_is_accepted(): void
    {
        $this->actingAs($this->makeProvider())
            ->post(route('provider.certifications.store'), $this->certificationData([
                'file' => UploadedFile::fake()->create('ok.pdf', 2048, 'application/pdf'),
            ]))
            ->assertSessionHasNoErrors();
    }

    public function test_certification_file_over_2mb_is_rejected(): void
    {
        $this->actingAs($this->makeProvider())
            ->post(route('provider.certifications.store'), $this->certificationData([
                'file' => UploadedFile::fake()->create('big.pdf', 2049, 'application/pdf'),
            ]))
            ->assertSessionHasErrors('file');
    }

    public function test_certification_file_of_a_wrong_type_is_rejected(): void
    {
        $this->actingAs($this->makeProvider())
            ->post(route('provider.certifications.store'), $this->certificationData([
                'file' => UploadedFile::fake()->create('program.exe', 10, 'application/x-msdownload'),
            ]))
            ->assertSessionHasErrors('file');
    }

    public function test_provider_can_edit_their_own_certification(): void
    {
        $provider = $this->makeProvider();
        $profile = $this->makeProfile($provider);
        $certification = $profile->certifications()->create($this->certificationData(['title' => 'Old title']));

        $this->actingAs($provider)->get(route('provider.certifications.edit', $certification))->assertOk();

        $this->actingAs($provider)
            ->put(route('provider.certifications.update', $certification), $this->certificationData(['title' => 'New title']))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('certifications', ['id' => $certification->id, 'title' => 'New title']);
    }

    public function test_provider_can_delete_their_own_certification_and_its_file(): void
    {
        $provider = $this->makeProvider();

        $this->actingAs($provider)->post(route('provider.certifications.store'), $this->certificationData([
            'file' => UploadedFile::fake()->create('certificate.pdf', 100, 'application/pdf'),
        ]));
        $certification = Certification::first();
        $path = $certification->file_path;

        $this->actingAs($provider)->delete(route('provider.certifications.destroy', $certification));

        $this->assertDatabaseCount('certifications', 0);
        Storage::disk('local')->assertMissing($path);
    }

    public function test_another_provider_cannot_open_edit_or_delete_someone_elses_certification(): void
    {
        $owner = $this->makeProvider();
        $certification = $this->makeProfile($owner)->certifications()->create($this->certificationData(['title' => 'Original']));

        $otherProvider = $this->makeProvider();
        $this->makeProfile($otherProvider);

        $this->actingAs($otherProvider)->get(route('provider.certifications.edit', $certification))->assertForbidden();
        $this->actingAs($otherProvider)
            ->put(route('provider.certifications.update', $certification), $this->certificationData(['title' => 'Hacked']))
            ->assertForbidden();
        $this->actingAs($otherProvider)->delete(route('provider.certifications.destroy', $certification))->assertForbidden();

        // Nothing changed.
        $this->assertDatabaseHas('certifications', ['id' => $certification->id, 'title' => 'Original']);
    }

    // =====================================================================
    // 6. Portfolio
    // =====================================================================

    public function test_provider_can_upload_a_portfolio_image_with_a_caption(): void
    {
        $this->actingAs($this->makeProvider())
            ->post(route('provider.portfolio.store'), [
                'image' => UploadedFile::fake()->image('work.jpg'),
                'caption' => 'Bathroom pipes',
            ])
            ->assertSessionHasNoErrors();

        $item = PortfolioItem::first();
        $this->assertSame('Bathroom pipes', $item->caption);
        Storage::disk('public')->assertExists($item->image_path);
    }

    public function test_the_12th_portfolio_image_is_accepted(): void
    {
        $provider = $this->makeProvider();
        $profile = $this->makeProfile($provider);
        $this->addPortfolioItems($profile, 11);

        $this->actingAs($provider)
            ->post(route('provider.portfolio.store'), ['image' => UploadedFile::fake()->image('work.jpg')])
            ->assertSessionHasNoErrors();

        $this->assertSame(12, $profile->portfolioItems()->count());
    }

    public function test_the_13th_portfolio_image_is_rejected(): void
    {
        $provider = $this->makeProvider();
        $profile = $this->makeProfile($provider);
        $this->addPortfolioItems($profile, 12);

        $this->actingAs($provider)
            ->post(route('provider.portfolio.store'), ['image' => UploadedFile::fake()->image('work.jpg')])
            ->assertSessionHasErrors('image');

        $this->assertSame(12, $profile->portfolioItems()->count());
    }

    public function test_portfolio_image_of_exactly_2mb_is_accepted(): void
    {
        $this->actingAs($this->makeProvider())
            ->post(route('provider.portfolio.store'), ['image' => UploadedFile::fake()->image('work.jpg')->size(2048)])
            ->assertSessionHasNoErrors();
    }

    public function test_portfolio_image_over_2mb_is_rejected(): void
    {
        $this->actingAs($this->makeProvider())
            ->post(route('provider.portfolio.store'), ['image' => UploadedFile::fake()->image('work.jpg')->size(2049)])
            ->assertSessionHasErrors('image');
    }

    public function test_portfolio_upload_without_an_image_is_rejected(): void
    {
        $this->actingAs($this->makeProvider())
            ->post(route('provider.portfolio.store'), ['caption' => 'No image here'])
            ->assertSessionHasErrors('image');
    }

    public function test_portfolio_caption_of_150_characters_is_accepted_and_151_is_rejected(): void
    {
        $provider = $this->makeProvider();

        $this->actingAs($provider)
            ->post(route('provider.portfolio.store'), [
                'image' => UploadedFile::fake()->image('a.jpg'),
                'caption' => str_repeat('a', 150),
            ])
            ->assertSessionHasNoErrors();

        $this->actingAs($provider)
            ->post(route('provider.portfolio.store'), [
                'image' => UploadedFile::fake()->image('b.jpg'),
                'caption' => str_repeat('a', 151),
            ])
            ->assertSessionHasErrors('caption');
    }

    public function test_provider_can_delete_their_own_portfolio_image_and_its_file(): void
    {
        $provider = $this->makeProvider();

        $this->actingAs($provider)->post(route('provider.portfolio.store'), [
            'image' => UploadedFile::fake()->image('work.jpg'),
        ]);
        $item = PortfolioItem::first();
        $path = $item->image_path;

        $this->actingAs($provider)->delete(route('provider.portfolio.destroy', $item));

        $this->assertDatabaseCount('portfolio_items', 0);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_another_provider_cannot_delete_someone_elses_portfolio_image(): void
    {
        $owner = $this->makeProvider();
        $item = $this->makeProfile($owner)->portfolioItems()->create(['image_path' => 'portfolio/owner.jpg']);

        $otherProvider = $this->makeProvider();
        $this->makeProfile($otherProvider);

        $this->actingAs($otherProvider)
            ->delete(route('provider.portfolio.destroy', $item))
            ->assertForbidden();

        $this->assertDatabaseCount('portfolio_items', 1);
    }

    // =====================================================================
    // 7. Completeness score
    // =====================================================================

    public function test_empty_profile_scores_0_percent(): void
    {
        $profile = $this->makeProfile($this->makeProvider());

        $this->assertSame(0, $profile->completeness());
    }

    public function test_photo_and_bio_together_score_30_percent(): void
    {
        $profile = $this->makeProfile($this->makeProvider(), [
            'photo_path' => 'profile-photos/me.jpg',
            'bio' => 'I fix pipes.',
        ]);

        $this->assertSame(30, $profile->completeness());
    }

    public function test_a_service_adds_25_percent(): void
    {
        $profile = $this->makeProfile($this->makeProvider());
        $profile->services()->attach($this->makeService()->id, ['price' => 1000]);

        $this->assertSame(25, $profile->completeness());
    }

    public function test_a_full_service_area_adds_20_percent_but_a_half_one_adds_nothing(): void
    {
        $halfArea = $this->makeProfile($this->makeProvider(), ['area_name' => 'Mirpur']);
        $this->assertSame(0, $halfArea->completeness());

        $fullArea = $this->makeProfile($this->makeProvider(), $this->areaData());
        $this->assertSame(20, $fullArea->completeness());
    }

    public function test_a_certification_adds_10_percent(): void
    {
        $profile = $this->makeProfile($this->makeProvider());
        $profile->certifications()->create($this->certificationData());

        $this->assertSame(10, $profile->completeness());
    }

    public function test_2_portfolio_images_give_no_points_but_3_give_15_percent(): void
    {
        $profile = $this->makeProfile($this->makeProvider());

        $this->addPortfolioItems($profile, 2);
        $this->assertSame(0, $profile->completeness());

        $this->addPortfolioItems($profile, 1);
        $this->assertSame(15, $profile->completeness());
    }

    public function test_complete_profile_scores_100_percent(): void
    {
        $profile = $this->makeProfile($this->makeProvider(), array_merge($this->areaData(), [
            'photo_path' => 'profile-photos/me.jpg',
            'bio' => 'I fix pipes.',
        ]));
        $profile->services()->attach($this->makeService()->id, ['price' => 1000]);
        $profile->certifications()->create($this->certificationData());
        $this->addPortfolioItems($profile, 3);

        $this->assertSame(100, $profile->completeness());
        $this->assertSame([], $profile->missingItems());
    }

    public function test_missing_items_list_has_all_6_items_for_an_empty_profile(): void
    {
        $profile = $this->makeProfile($this->makeProvider());

        $this->assertCount(6, $profile->missingItems());
    }

    public function test_my_profile_page_shows_the_score(): void
    {
        $provider = $this->makeProvider();
        $this->makeProfile($provider, ['photo_path' => 'profile-photos/me.jpg', 'bio' => 'I fix pipes.']);

        $this->actingAs($provider)
            ->get(route('provider.profile.edit'))
            ->assertOk()
            ->assertSee('Profile completeness: 30%');
    }
}
