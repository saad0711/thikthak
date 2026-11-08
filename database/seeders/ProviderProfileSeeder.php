<?php

namespace Database\Seeders;

use App\Models\ProviderProfile;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

/** Demo profile data. Run after UserSeeder and CatalogSeeder. */
class ProviderProfileSeeder extends Seeder
{
    public function run(): void
    {
        $provider = User::query()->where('email', 'provider@thikthak.test')->first();

        if (! $provider || ! $provider->isProvider()) {
            throw new RuntimeException('Run UserSeeder first; provider@thikthak.test must exist as a provider.');
        }

        $profile = ProviderProfile::firstOrCreate(['user_id' => $provider->id]);
        $profile->fill([
            'bio' => 'I have repaired pipes, taps and water tanks in Dhaka for six years. I arrive on time and clean up after the job.',
            'years_experience' => 6,
            'area_name' => 'Mirpur, Dhaka',
            'latitude' => 23.8069,
            'longitude' => 90.3687,
            'radius_km' => 10,
        ])->save();

        // These are actual F1 service names and the prices are inside F1's ranges:
        // Pipe leak repair: BDT 300-1,000; AC servicing: BDT 800-1,500.
        $pipeRepair = Service::query()->where('name', 'Pipe leak repair')->first();
        $acServicing = Service::query()->where('name', 'AC servicing')->first();

        if (! $pipeRepair || ! $acServicing) {
            throw new RuntimeException('Run CatalogSeeder first; Pipe leak repair and AC servicing must exist.');
        }

        $profile->services()->sync([
            $pipeRepair->id => ['price' => 900],
            $acServicing->id => ['price' => 1400],
        ]);

        $profile->certifications()->firstOrCreate(
            ['title' => 'Certified Plumber', 'issuer' => 'Dhaka Technical Training Centre', 'year' => 2019]
        );
    }
}
