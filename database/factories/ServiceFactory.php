<?php

namespace Database\Factories;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $min = fake()->numberBetween(2, 40) * 100;

        return [
            'service_category_id' => ServiceCategory::factory(),
            'name' => ucfirst(fake()->unique()->words(3, true)),
            'description' => fake()->sentence(12),
            'price_min' => $min,
            'price_max' => $min + fake()->numberBetween(1, 30) * 100,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function priced(int $min, int $max): static
    {
        return $this->state(fn () => ['price_min' => $min, 'price_max' => $max]);
    }
}
