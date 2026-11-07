<?php

namespace Tests\Feature\Catalog;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private ServiceCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->category = ServiceCategory::factory()->create(['name' => 'AC Repair']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function formData(array $overrides = []): array
    {
        return array_merge([
            'service_category_id' => $this->category->id,
            'name' => 'AC Servicing',
            'description' => 'Full cleaning of indoor and outdoor units.',
            'price_min' => 800,
            'price_max' => 1500,
            'is_active' => '1',
        ], $overrides);
    }

    public function test_admin_can_create_a_service(): void
    {
        $this->actingAs($this->admin)->post('/admin/services', $this->formData())->assertSessionHasNoErrors();

        $this->assertDatabaseHas('services', [
            'name' => 'AC Servicing',
            'slug' => 'ac-servicing',
            'service_category_id' => $this->category->id,
            'price_min' => 800,
            'price_max' => 1500,
        ]);
    }

    public function test_minimum_price_cannot_be_above_maximum_price(): void
    {
        $this->actingAs($this->admin)->post('/admin/services', $this->formData(['price_min' => 2000, 'price_max' => 1000]))
            ->assertSessionHasErrors('price_max');

        $this->assertDatabaseCount('services', 0);
    }

    public function test_service_name_must_be_unique_within_its_category_only(): void
    {
        Service::factory()->for($this->category, 'category')->create(['name' => 'AC Servicing']);
        $other = ServiceCategory::factory()->create();

        $this->actingAs($this->admin)->post('/admin/services', $this->formData())->assertSessionHasErrors('name');

        $this->actingAs($this->admin)->post('/admin/services', $this->formData(['service_category_id' => $other->id]))
            ->assertSessionHasNoErrors();
    }

    public function test_same_name_in_two_categories_gets_two_different_slugs(): void
    {
        $other = ServiceCategory::factory()->create();
        $first = Service::factory()->for($this->category, 'category')->create(['name' => 'Installation']);
        $second = Service::factory()->for($other, 'category')->create(['name' => 'Installation']);

        $this->assertSame('installation', $first->slug);
        $this->assertSame('installation-2', $second->slug);
    }

    public function test_admin_can_deactivate_a_service(): void
    {
        $service = Service::factory()->for($this->category, 'category')->create();

        $this->actingAs($this->admin)->put("/admin/services/{$service->id}", $this->formData(['is_active' => '0']))
            ->assertSessionHasNoErrors();

        $this->assertFalse($service->fresh()->is_active);
    }

    public function test_service_that_customers_requested_cannot_be_deleted(): void
    {
        $service = Service::factory()->for($this->category, 'category')->create();
        ServiceRequest::factory()->for($service)->create();

        $this->actingAs($this->admin)->delete("/admin/services/{$service->id}")->assertSessionHas('error');

        $this->assertModelExists($service);
    }

    public function test_unused_service_can_be_deleted(): void
    {
        $service = Service::factory()->for($this->category, 'category')->create();

        $this->actingAs($this->admin)->delete("/admin/services/{$service->id}")->assertSessionHas('status');

        $this->assertModelMissing($service);
    }
}
