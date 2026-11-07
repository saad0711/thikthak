<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

/**
 * F1 starter catalog: 8 categories with services and indicative BDT price ranges.
 */
class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            ['Plumbing', '🔧', 'Leaks, taps, pipes, bathroom fittings and water tanks.', [
                ['Pipe leak repair', 300, 1000, 'Find and fix leaking pipes under sinks, in walls or on the roof.'],
                ['Tap and faucet installation', 200, 600, 'Install or replace kitchen and bathroom taps.'],
                ['Bathroom fitting installation', 1000, 4000, 'Commode, basin, shower and other bathroom fittings.'],
                ['Water tank cleaning', 800, 2500, 'Drain, scrub and disinfect overhead or underground water tanks.'],
            ]],
            ['Electrical', '💡', 'Wiring, fans, lights, sockets and IPS.', [
                ['Wiring repair', 300, 1500, 'Trace and fix faulty house wiring and short circuits.'],
                ['Ceiling fan installation', 300, 700, 'Install, replace or repair ceiling fans.'],
                ['Switch and socket repair', 150, 500, 'Replace broken switches, sockets and switch boards.'],
                ['IPS installation', 800, 2500, 'Install an IPS with its battery and connect it to your lines.'],
            ]],
            ['AC Repair', '❄️', 'Servicing, gas refills, installation and repair for split and window ACs.', [
                ['AC servicing', 800, 1500, 'Full cleaning of indoor and outdoor units for better cooling.'],
                ['AC gas refill', 2500, 4500, 'Leak check and refrigerant top-up.'],
                ['AC installation', 2000, 4500, 'Install a new split or window AC, including piping.'],
                ['AC repair', 1000, 3500, 'Diagnose and fix cooling, noise, water leaks or power problems.'],
            ]],
            ['Cleaning', '🧹', 'Home, kitchen, bathroom and sofa cleaning.', [
                ['Home deep cleaning', 2500, 7000, 'Top-to-bottom cleaning of a flat, room by room.'],
                ['Kitchen deep cleaning', 1500, 3500, 'Degrease cabinets, stove, chimney, tiles and sink.'],
                ['Bathroom deep cleaning', 800, 2000, 'Remove stains and scale from tiles, fittings and floors.'],
                ['Sofa cleaning', 800, 2500, 'Shampoo and vacuum fabric sofas, priced by seats.'],
            ]],
            ['Painting', '🎨', 'Interior and exterior painting and wall repair.', [
                ['Room painting', 3000, 9000, 'Paint one room: walls and ceiling, with putty touch-ups.'],
                ['Full flat painting', 25000, 80000, 'Paint a whole flat, priced by size and paint brand.'],
                ['Door and window painting', 500, 2000, 'Paint or varnish wooden and metal doors and windows.'],
                ['Damp wall treatment', 2000, 8000, 'Treat seepage and damp patches before painting.'],
            ]],
            ['Tutoring', '📚', 'Home tutors for school, college and admission tests.', [
                ['Primary school tutor', 3000, 6000, 'Monthly home tutoring for classes 1–5.'],
                ['SSC tutor', 5000, 10000, 'Monthly tutoring for SSC subjects, Bangla or English medium.'],
                ['HSC tutor', 6000, 12000, 'Monthly tutoring for HSC science, commerce or arts.'],
                ['University admission coaching', 8000, 15000, 'One-to-one preparation for admission tests.'],
            ]],
            ['Photography', '📷', 'Event, portrait and product photography.', [
                ['Event photography', 5000, 25000, 'Birthdays, holud, wedding receptions and corporate events.'],
                ['Portrait photoshoot', 2000, 8000, 'Outdoor or studio portraits with edited photos.'],
                ['Product photography', 1500, 6000, 'Clean product photos for online shops, priced per set.'],
            ]],
            ['Event Management', '🎉', 'Planning, decoration and catering for events.', [
                ['Birthday decoration', 3000, 15000, 'Balloons, backdrop, lights and table setup at home or a venue.'],
                ['Holud and wedding decoration', 20000, 150000, 'Stage, flowers, lighting and entrance decoration.'],
                ['Catering arrangement', 10000, 100000, 'Food and service for home or venue events, priced by guests.'],
            ]],
        ];

        foreach ($catalog as $categoryOrder => [$name, $icon, $description, $services]) {
            $category = ServiceCategory::updateOrCreate(
                ['name' => $name],
                ['icon' => $icon, 'description' => $description, 'is_active' => true, 'sort_order' => $categoryOrder * 10],
            );

            foreach ($services as $serviceOrder => [$serviceName, $min, $max, $serviceDescription]) {
                $category->services()->updateOrCreate(
                    ['name' => $serviceName],
                    ['description' => $serviceDescription, 'price_min' => $min, 'price_max' => $max, 'is_active' => true, 'sort_order' => $serviceOrder * 10],
                );
            }
        }
    }
}
