<?php

namespace Tests\Feature\Catalog;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_sees_the_category_list(): void
    {
        ServiceCategory::factory()->create(['name' => 'Plumbing']);

        $this->actingAs($this->admin)->get('/admin/categories')->assertOk()->assertSee('Plumbing');
    }

    public function test_admin_can_create_a_category_and_its_slug_is_generated(): void
    {
        $this->actingAs($this->admin)->post('/admin/categories', [
            'name' => 'AC Repair',
            'description' => 'Servicing and repair for ACs.',
            'icon' => '❄️',
            'sort_order' => 5,
            'is_active' => '1',
        ])->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('service_categories', ['name' => 'AC Repair', 'slug' => 'ac-repair', 'is_active' => true]);
    }

    public function test_duplicate_category_name_is_rejected(): void
    {
        ServiceCategory::factory()->create(['name' => 'Cleaning']);

        $this->actingAs($this->admin)->post('/admin/categories', ['name' => 'Cleaning'])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('service_categories', 1);
    }

    public function test_admin_can_edit_and_deactivate_a_category(): void
    {
        $category = ServiceCategory::factory()->create(['name' => 'Paintng']);

        $this->actingAs($this->admin)->put("/admin/categories/{$category->id}", [
            'name' => 'Painting',
            'is_active' => '0',
        ])->assertRedirect(route('admin.categories.index'));

        $category->refresh();
        $this->assertSame('Painting', $category->name);
        $this->assertSame('painting', $category->slug);
        $this->assertFalse($category->is_active);
    }

    public function test_category_with_services_cannot_be_deleted(): void
    {
        $category = ServiceCategory::factory()->has(Service::factory())->create();

        $this->actingAs($this->admin)
            ->from('/admin/categories')
            ->delete("/admin/categories/{$category->id}")
            ->assertRedirect('/admin/categories')
            ->assertSessionHas('error');

        $this->assertModelExists($category);
    }

    public function test_empty_category_can_be_deleted(): void
    {
        $category = ServiceCategory::factory()->create();

        $this->actingAs($this->admin)->delete("/admin/categories/{$category->id}")->assertSessionHas('status');

        $this->assertModelMissing($category);
    }

    public function test_inactive_category_is_hidden_from_customers_but_not_from_admin(): void
    {
        $hidden = ServiceCategory::factory()->inactive()->create(['name' => 'Secret Category']);
        Service::factory()->for($hidden, 'category')->create(['name' => 'Hidden Service']);

        $this->get('/')->assertOk()->assertDontSee('Secret Category');
        $this->get("/categories/{$hidden->slug}")->assertNotFound();
        $this->get('/services')->assertOk()->assertDontSee('Hidden Service');

        $this->actingAs($this->admin)->get('/admin/categories')->assertSee('Secret Category');
    }

    public function test_customer_cannot_manage_categories(): void
    {
        $this->actingAs(User::factory()->customer()->create())
            ->post('/admin/categories', ['name' => 'Sneaky'])
            ->assertForbidden();

        $this->assertDatabaseCount('service_categories', 0);
    }
}
