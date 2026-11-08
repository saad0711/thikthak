<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ServiceRequestStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $requestCounts = ServiceRequest::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.dashboard', [
            'customers' => User::where('role', UserRole::Customer)->count(),
            'providers' => User::where('role', UserRole::Provider)->count(),
            'categories' => ServiceCategory::count(),
            'activeCategories' => ServiceCategory::active()->count(),
            'services' => Service::count(),
            'activeServices' => Service::active()->count(),
            'requestCounts' => collect(ServiceRequestStatus::cases())
                ->mapWithKeys(fn (ServiceRequestStatus $status) => [$status->value => (int) ($requestCounts[$status->value] ?? 0)]),
        ]);
    }
}
