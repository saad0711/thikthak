<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('provider.dashboard', [
            'user' => $request->user(),
            'profile' => $request->user()->providerProfile,
        ]);
    }
}
