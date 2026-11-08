<?php

namespace Database\Seeders;

use App\Enums\ServiceRequestStatus;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * F4 demo data: a few requests from the demo customer, in different statuses.
 */
class ServiceRequestSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::where('email', 'customer@thikthak.test')->first();

        if (! $customer || $customer->serviceRequests()->exists()) {
            return;
        }

        $requests = [
            ['AC servicing', 'Bedroom AC is not cooling', 'The 1.5 ton split AC in the bedroom runs but barely cools. It was last serviced over a year ago.', 800, 1500, 5, ServiceRequestStatus::Open],
            ['Pipe leak repair', 'Leak under the kitchen sink', 'Water drips from the pipe joint under the kitchen sink and the cabinet floor is getting damaged.', 300, 800, 3, ServiceRequestStatus::Open],
            ['SSC tutor', 'Math and physics tutor for SSC', 'Looking for a tutor three days a week for SSC math and physics, Bangla medium, evenings after 5 pm.', 5000, 8000, 14, ServiceRequestStatus::Quoted],
            ['Sofa cleaning', 'Clean a 5-seat fabric sofa', 'Five-seat fabric sofa with a few tea stains. Prefer a weekend morning.', 1000, 2000, 7, ServiceRequestStatus::Cancelled],
        ];

        foreach ($requests as [$serviceName, $title, $description, $min, $max, $days, $status]) {
            $service = Service::where('name', $serviceName)->first();

            if (! $service) {
                continue;
            }

            $serviceRequest = $customer->serviceRequests()->make([
                'service_id' => $service->id,
                'title' => $title,
                'description' => $description,
                'budget_min' => $min,
                'budget_max' => $max,
                'deadline' => today()->addDays($days),
                'area_name' => $customer->area ?? 'Dhanmondi',
            ]);
            $serviceRequest->status = $status;

            if ($status === ServiceRequestStatus::Cancelled) {
                $serviceRequest->cancelled_at = now();
                $serviceRequest->cancel_reason = 'Found someone through a neighbour.';
            }

            $serviceRequest->save();
        }
    }
}
