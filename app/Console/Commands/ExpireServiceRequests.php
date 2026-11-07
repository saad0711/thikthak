<?php

namespace App\Console\Commands;

use App\Enums\ServiceRequestStatus;
use App\Models\ServiceRequest;
use Illuminate\Console\Command;

/**
 * F4: marks Open or Quoted requests whose deadline has passed as Expired.
 * Scheduled hourly in routes/console.php. Run it by hand with `php artisan requests:expire`.
 */
class ExpireServiceRequests extends Command
{
    protected $signature = 'requests:expire';

    protected $description = 'Mark open or quoted service requests whose deadline has passed as expired';

    public function handle(): int
    {
        $expired = 0;

        ServiceRequest::query()->overdue()->chunkById(100, function ($requests) use (&$expired) {
            foreach ($requests as $serviceRequest) {
                $serviceRequest->transitionTo(ServiceRequestStatus::Expired);
                $expired++;
            }
        });

        $this->info("Expired {$expired} service request(s).");

        return self::SUCCESS;
    }
}
