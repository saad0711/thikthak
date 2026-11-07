<?php

namespace Tests\Feature\ServiceRequests;

use App\Enums\ServiceRequestStatus;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PostServiceRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->travelTo(now()->setDate(2026, 11, 7)->setTime(10, 0));

        $this->customer = User::factory()->customer()->create();
        $this->service = Service::factory()->create(['name' => 'Pipe leak repair']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function formData(array $overrides = []): array
    {
        return array_merge([
            'service_id' => $this->service->id,
            'title' => 'Leak under the kitchen sink',
            'description' => 'Water drips from the pipe joint under the sink and the cabinet is getting wet.',
            'budget_min' => 300,
            'budget_max' => 800,
            'deadline' => '2026-11-12',
            'area_name' => 'Dhanmondi',
        ], $overrides);
    }

    /**
     * @return array<int, UploadedFile>
     */
    private function photos(int $count): array
    {
        return array_map(fn (int $i) => UploadedFile::fake()->image("photo{$i}.jpg", 640, 480)->size(300), range(1, $count));
    }

    public function test_post_request_page_loads_with_catalog_services(): void
    {
        $this->actingAs($this->customer)->get('/requests/create')->assertOk()->assertSee('Pipe leak repair');
    }

    public function test_customer_can_post_a_request_with_photos(): void
    {
        $response = $this->actingAs($this->customer)->post('/requests', $this->formData(['photos' => $this->photos(2)]));

        $serviceRequest = ServiceRequest::firstOrFail();
        $response->assertRedirect(route('requests.show', $serviceRequest));

        $this->assertSame($this->customer->id, $serviceRequest->customer_id);
        $this->assertSame(ServiceRequestStatus::Open, $serviceRequest->status);
        $this->assertSame('Dhanmondi', $serviceRequest->area_name);
        $this->assertEquals(config('thikthak.areas.Dhanmondi')[0], (float) $serviceRequest->latitude);
        $this->assertCount(2, $serviceRequest->photos);
        foreach ($serviceRequest->photos as $photo) {
            Storage::disk('public')->assertExists($photo->path);
        }

        $this->actingAs($this->customer)->get('/requests')->assertSee('Leak under the kitchen sink');
    }

    public function test_minimum_budget_cannot_be_above_maximum(): void
    {
        $this->actingAs($this->customer)->post('/requests', $this->formData(['budget_min' => 2000, 'budget_max' => 1000]))
            ->assertSessionHasErrors('budget_max');

        $this->assertDatabaseCount('service_requests', 0);
    }

    public function test_budget_must_be_more_than_zero(): void
    {
        $this->actingAs($this->customer)->post('/requests', $this->formData(['budget_min' => 0]))
            ->assertSessionHasErrors('budget_min');
    }

    /**
     * Today is 2026-11-07 in these tests.
     *
     * @return array<string, array{string, bool}>
     */
    public static function deadlines(): array
    {
        return [
            'today is too soon' => ['2026-11-07', false],
            'tomorrow is the earliest' => ['2026-11-08', true],
            '60 days ahead is the latest' => ['2027-01-06', true],
            '61 days ahead is too far' => ['2027-01-07', false],
        ];
    }

    #[DataProvider('deadlines')]
    public function test_deadline_must_be_between_tomorrow_and_60_days_ahead(string $deadline, bool $accepted): void
    {
        $response = $this->actingAs($this->customer)->post('/requests', $this->formData(['deadline' => $deadline]));

        if ($accepted) {
            $response->assertSessionHasNoErrors();
            $this->assertDatabaseCount('service_requests', 1);
        } else {
            $response->assertSessionHasErrors('deadline');
            $this->assertDatabaseCount('service_requests', 0);
        }
    }

    public function test_five_photos_are_allowed_but_six_are_not(): void
    {
        $this->actingAs($this->customer)->post('/requests', $this->formData(['photos' => $this->photos(5)]))
            ->assertSessionHasNoErrors();

        $this->actingAs($this->customer)->post('/requests', $this->formData(['photos' => $this->photos(6)]))
            ->assertSessionHasErrors('photos');

        $this->assertDatabaseCount('service_requests', 1);
    }

    public function test_photo_must_be_an_image_of_at_most_2_mb(): void
    {
        $this->actingAs($this->customer)->post('/requests', $this->formData([
            'photos' => [UploadedFile::fake()->image('big.jpg')->size(3000)],
        ]))->assertSessionHasErrors('photos.0');

        $this->actingAs($this->customer)->post('/requests', $this->formData([
            'photos' => [UploadedFile::fake()->create('quote.pdf', 100, 'application/pdf')],
        ]))->assertSessionHasErrors('photos.0');

        $this->assertDatabaseCount('service_requests', 0);
    }

    public function test_inactive_service_cannot_be_requested(): void
    {
        $inactive = Service::factory()->inactive()->create();
        $inHiddenCategory = Service::factory()->for(ServiceCategory::factory()->inactive(), 'category')->create();

        $this->actingAs($this->customer)->post('/requests', $this->formData(['service_id' => $inactive->id]))
            ->assertSessionHasErrors('service_id');
        $this->actingAs($this->customer)->post('/requests', $this->formData(['service_id' => $inHiddenCategory->id]))
            ->assertSessionHasErrors('service_id');

        $this->assertDatabaseCount('service_requests', 0);
    }

    public function test_area_must_come_from_the_list(): void
    {
        $this->actingAs($this->customer)->post('/requests', $this->formData(['area_name' => 'Atlantis']))
            ->assertSessionHasErrors('area_name');
    }

    public function test_provider_cannot_post_a_request(): void
    {
        $this->actingAs(User::factory()->provider()->create())
            ->post('/requests', $this->formData())
            ->assertForbidden();

        $this->assertDatabaseCount('service_requests', 0);
    }

    public function test_guest_is_sent_to_login(): void
    {
        $this->post('/requests', $this->formData())->assertRedirect(route('login'));
    }
}
