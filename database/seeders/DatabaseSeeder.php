<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database: `php artisan migrate --seed`.
     *
     * Model events stay on, so category and service slugs are generated as usual.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CatalogSeeder::class,
            ServiceRequestSeeder::class,
            AvailabilitySeeder::class,
        ]);
    }
}
