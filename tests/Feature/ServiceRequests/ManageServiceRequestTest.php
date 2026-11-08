<?php

namespace Tests\Feature\ServiceRequests;

use App\Enums\ServiceRequestStatus;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ManageServiceRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->customer = User::factory()->customer()->create();
    }

    private function requestFor(User $customer, ServiceRequestStatus $status = ServiceRequestStatus::Open): ServiceRequest
    {
        return ServiceRequest::factory()->for($customer, 'customer')->withStatus($status)->create();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function editData(ServiceRequest $serviceRequest, array $overrides = []): array
    {
        return array_merge([
            'service_id' => $serviceRequest->service_id,
            'title' => 'Updated title for the job',
            'description' => $serviceRequest->description.' Updated with more detail.',
            'budget_min' => 500,
            'budget_max' => 900,
            'deadline' => today()->addDays(10)->toDateString(),
            'area_name' => 'Gulshan',
        ], $overrides);
    }

    public function test_my_requests_lists_only_my_own_requests(): void
    {
        $mine = $this->requestFor($this->customer);
        $theirs = $this->requestFor(User::factory()->customer()->create());

        $this->actingAs($this->customer)->get('/requests')
            ->assertOk()
            ->assertSee($mine->title)
            ->assertDontSee($theirs->title);
    }

    public function test_my_requests_can_be_filtered_by_status(): void
    {
        $open = $this->requestFor($this->customer);
        $cancelled = $this->requestFor($this->customer, ServiceRequestStatus::Cancelled);

        $this->actingAs($this->customer)->get('/requests?status=cancelled')
            ->assertSee($cancelled->title)
            ->assertDontSee($open->title);
    }

    public function test_customer_can_view_their_own_request(): void
    {
        $serviceRequest = $this->requestFor($this->customer);

        $this->actingAs($this->customer)->get("/requests/{$serviceRequest->id}")->assertOk()->assertSee($serviceRequest->title);
    }

    public function test_customer_cannot_view_someone_elses_request(): void
    {
        $theirs = $this->requestFor(User::factory()->customer()->create());

        $this->actingAs($this->customer)->get("/requests/{$theirs->id}")->assertForbidden();
    }

    public function test_open_request_can_be_edited(): void
    {
        $serviceRequest = $this->requestFor($this->customer);

        $this->actingAs($this->customer)->get("/requests/{$serviceRequest->id}/edit")->assertOk();
        $this->actingAs($this->customer)->put("/requests/{$serviceRequest->id}", $this->editData($serviceRequest))
            ->assertRedirect(route('requests.show', $serviceRequest));

        $serviceRequest->refresh();
        $this->assertSame('Updated title for the job', $serviceRequest->title);
        $this->assertSame('Gulshan', $serviceRequest->area_name);
        $this->assertEquals(config('thikthak.areas.Gulshan')[0], (float) $serviceRequest->latitude);
    }

    public function test_quoted_request_can_no_longer_be_edited(): void
    {
        $serviceRequest = $this->requestFor($this->customer, ServiceRequestStatus::Quoted);

        $this->actingAs($this->customer)->get("/requests/{$serviceRequest->id}/edit")->assertForbidden();
        $this->actingAs($this->customer)->put("/requests/{$serviceRequest->id}", $this->editData($serviceRequest))
            ->assertForbidden();

        $this->assertNotSame('Updated title for the job', $serviceRequest->fresh()->title);
    }

    public function test_photos_can_be_removed_and_added_while_editing(): void
    {
        $serviceRequest = $this->requestFor($this->customer);
        $old = $serviceRequest->photos()->create(['path' => UploadedFile::fake()->image('old.jpg')->store('service-requests', 'public')]);

        $this->actingAs($this->customer)->put("/requests/{$serviceRequest->id}", $this->editData($serviceRequest, [
            'remove_photos' => [$old->id],
            'photos' => [UploadedFile::fake()->image('new.jpg')],
        ]))->assertSessionHasNoErrors();

        $this->assertModelMissing($old);
        Storage::disk('public')->assertMissing($old->path);
        $this->assertCount(1, $serviceRequest->photos()->get());
    }

    public function test_editing_cannot_take_photos_above_five(): void
    {
        $serviceRequest = $this->requestFor($this->customer);
        foreach (range(1, 4) as $i) {
            $serviceRequest->photos()->create(['path' => "service-requests/existing-{$i}.jpg"]);
        }

        $this->actingAs($this->customer)->put("/requests/{$serviceRequest->id}", $this->editData($serviceRequest, [
            'photos' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')],
        ]))->assertSessionHasErrors('photos');

        $this->assertCount(4, $serviceRequest->photos()->get());
    }

    public function test_cannot_remove_a_photo_that_belongs_to_another_request(): void
    {
        $serviceRequest = $this->requestFor($this->customer);
        $otherPhoto = $this->requestFor(User::factory()->customer()->create())
            ->photos()->create(['path' => 'service-requests/other.jpg']);

        $this->actingAs($this->customer)->put("/requests/{$serviceRequest->id}", $this->editData($serviceRequest, [
            'remove_photos' => [$otherPhoto->id],
        ]))->assertSessionHasErrors('remove_photos.0');

        $this->assertModelExists($otherPhoto);
    }

    public function test_open_request_can_be_cancelled_with_a_reason(): void
    {
        $serviceRequest = $this->requestFor($this->customer);

        $this->actingAs($this->customer)->post("/requests/{$serviceRequest->id}/cancel", ['cancel_reason' => 'Fixed it myself.'])
            ->assertRedirect(route('requests.show', $serviceRequest));

        $serviceRequest->refresh();
        $this->assertSame(ServiceRequestStatus::Cancelled, $serviceRequest->status);
        $this->assertNotNull($serviceRequest->cancelled_at);
        $this->assertSame('Fixed it myself.', $serviceRequest->cancel_reason);
    }

    public function test_quoted_request_can_be_cancelled(): void
    {
        $serviceRequest = $this->requestFor($this->customer, ServiceRequestStatus::Quoted);

        $this->actingAs($this->customer)->post("/requests/{$serviceRequest->id}/cancel")->assertSessionHasNoErrors();

        $this->assertSame(ServiceRequestStatus::Cancelled, $serviceRequest->fresh()->status);
    }

    public function test_assigned_request_cannot_be_cancelled(): void
    {
        $serviceRequest = $this->requestFor($this->customer, ServiceRequestStatus::Assigned);

        $this->actingAs($this->customer)->post("/requests/{$serviceRequest->id}/cancel")->assertForbidden();

        $this->assertSame(ServiceRequestStatus::Assigned, $serviceRequest->fresh()->status);
    }

    public function test_customer_cannot_cancel_someone_elses_request(): void
    {
        $theirs = $this->requestFor(User::factory()->customer()->create());

        $this->actingAs($this->customer)->post("/requests/{$theirs->id}/cancel")->assertForbidden();

        $this->assertSame(ServiceRequestStatus::Open, $theirs->fresh()->status);
    }

    public function test_customer_dashboard_shows_request_counts(): void
    {
        $this->requestFor($this->customer);
        $this->requestFor($this->customer);

        $response = $this->actingAs($this->customer)->get('/customer/dashboard')->assertOk();

        $this->assertSame(2, $response->viewData('counts')['open']);
    }
}
