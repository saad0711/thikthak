<?php

namespace App\Http\Controllers\Customer;

use App\Enums\ServiceRequestStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $requests = $request->user()->serviceRequests();

        $counts = (clone $requests)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('customer.dashboard', [
            'counts' => collect(ServiceRequestStatus::cases())
                ->mapWithKeys(fn (ServiceRequestStatus $status) => [$status->value => (int) ($counts[$status->value] ?? 0)]),
            'recent' => (clone $requests)->with('service')->latest()->take(5)->get(),
        ]);
    }
}
