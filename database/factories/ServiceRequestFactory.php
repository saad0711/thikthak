<?php

namespace Database\Factories;

use App\Enums\ServiceRequestStatus;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceRequest>
 */
class ServiceRequestFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $min = fake()->numberBetween(3, 30) * 100;

        return [
            'customer_id' => User::factory()->customer(),
            'service_id' => Service::factory(),
            'title' => ucfirst(fake()->words(4, true)),
            'description' => fake()->paragraph(),
            'budget_min' => $min,
            'budget_max' => $min + fake()->numberBetween(1, 20) * 100,
            'deadline' => today()->addDays(fake()->numberBetween(2, 30)),
            'area_name' => fake()->randomElement(array_keys(config('thikthak.areas'))),
            'status' => ServiceRequestStatus::Open,
        ];
    }

    public function withStatus(ServiceRequestStatus $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }

    /**
     * Still Open, but its deadline day has already passed.
     */
    public function overdue(): static
    {
        return $this->state(fn () => ['deadline' => today()->subDay()]);
    }
}
