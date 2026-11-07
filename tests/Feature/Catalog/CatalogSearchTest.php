<?php

namespace Tests\Feature\Catalog;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogSearchTest extends TestCase
{
    use RefreshDatabase;

    private ServiceCategory $acRepair;

    private ServiceCategory $cleaning;

    protected function setUp(): void
    {
        parent::setUp();

        // Descriptions are chosen so the letters "ac" only appear in the AC services.
        $this->acRepair = ServiceCategory::factory()->create(['name' => 'AC Repair', 'description' => null]);
        $this->cleaning = ServiceCategory::factory()->create(['name' => 'Cleaning', 'description' => null]);

        Service::factory()->for($this->acRepair, 'category')->priced(800, 1500)->create(['name' => 'AC Servicing', 'description' => 'Indoor unit wash.']);
        Service::factory()->for($this->acRepair, 'category')->priced(2500, 4500)->create(['name' => 'Gas Refill', 'description' => 'Top up the gas.']);
        Service::factory()->for($this->cleaning, 'category')->priced(800, 2500)->create(['name' => 'Sofa Cleaning', 'description' => 'Shampoo for sofas.']);
        Service::factory()->for($this->cleaning, 'category')->priced(2500, 7000)->create(['name' => 'Home Deep Cleaning', 'description' => 'Room by room.']);
        Service::factory()->for($this->cleaning, 'category')->priced(100, 400)->create(['name' => 'Window Wipe', 'description' => 'Glass only.']);
    }

    public function test_home_page_lists_active_categories(): void
    {
        $this->get('/')->assertOk()->assertSee('AC Repair')->assertSee('Cleaning');
    }

    public function test_keyword_search_matches_service_and_category_names_ignoring_case(): void
    {
        $this->get('/services?q=ac')
            ->assertOk()
            ->assertSee('AC Servicing')
            ->assertSee('Gas Refill') // found through its category, "AC Repair"
            ->assertDontSee('Sofa Cleaning');

        $this->get('/services?q=SOFA')->assertSee('Sofa Cleaning')->assertDontSee('AC Servicing');
    }

    public function test_keyword_matches_the_start_of_words_not_the_middle(): void
    {
        Service::factory()->for($this->cleaning, 'category')->create(['name' => 'Switch Repair', 'description' => 'Replace broken switches.']);

        $this->get('/services?q=ac')->assertSee('AC Servicing')->assertDontSee('Switch Repair');
        $this->get('/services?q=broken')->assertSee('Switch Repair');
    }

    public function test_category_filter_shows_only_that_category(): void
    {
        $this->get('/services?category='.$this->cleaning->id)
            ->assertOk()
            ->assertSee('Sofa Cleaning')
            ->assertSee('Home Deep Cleaning')
            ->assertDontSee('AC Servicing');
    }

    public function test_price_filter_returns_services_whose_range_overlaps(): void
    {
        $this->get('/services?min_price=500&max_price=1500')
            ->assertOk()
            ->assertSee('AC Servicing')      // 800–1500
            ->assertSee('Sofa Cleaning')     // 800–2500
            ->assertDontSee('Gas Refill')    // 2500–4500
            ->assertDontSee('Home Deep Cleaning') // 2500–7000
            ->assertDontSee('Window Wipe');  // 100–400
    }

    public function test_empty_search_lists_all_active_services_paginated(): void
    {
        Service::factory()->count(10)->for($this->cleaning, 'category')->create();

        $firstPage = $this->get('/services')->assertOk()->assertSee('15 services found');
        $this->assertCount(12, $firstPage->viewData('services')->items());

        $this->assertCount(3, $this->get('/services?page=2')->viewData('services')->items());
    }

    public function test_minimum_price_above_maximum_is_a_validation_error(): void
    {
        $this->get('/services?min_price=2000&max_price=500')
            ->assertRedirect(route('catalog.search'))
            ->assertSessionHasErrors('max_price');
    }

    public function test_search_with_no_matches_shows_the_empty_state(): void
    {
        $this->get('/services?q=xyzabc')->assertOk()->assertSee('No services found');
    }

    public function test_sql_looking_input_is_treated_as_plain_text(): void
    {
        $this->get('/services?q='.urlencode("' OR 1=1 --"))->assertOk()->assertSee('No services found');

        $this->assertDatabaseCount('services', 5);
    }

    public function test_inactive_service_is_hidden_from_customers(): void
    {
        Service::factory()->for($this->cleaning, 'category')->inactive()->create(['name' => 'Retired Service']);

        $this->get('/services')->assertDontSee('Retired Service');
        $this->get("/categories/{$this->cleaning->slug}")->assertOk()->assertDontSee('Retired Service');
    }

    public function test_results_can_be_sorted_by_price(): void
    {
        $this->get('/services?sort=price_asc')
            ->assertSeeInOrder(['Window Wipe', 'AC Servicing', 'Gas Refill']);

        $this->get('/services?sort=price_desc')
            ->assertSeeInOrder(['Home Deep Cleaning', 'Gas Refill', 'Window Wipe']);
    }

    public function test_category_page_lists_its_services(): void
    {
        $this->get("/categories/{$this->acRepair->slug}")
            ->assertOk()
            ->assertSee('AC Servicing')
            ->assertSee('Gas Refill')
            ->assertDontSee('Sofa Cleaning');
    }
}
