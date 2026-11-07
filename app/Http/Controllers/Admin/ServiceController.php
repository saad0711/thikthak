<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ServiceFormRequest;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * F1: admin management of the services inside each category.
 */
class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $categoryId = $request->integer('category') ?: null;

        return view('admin.services.index', [
            'services' => Service::query()
                ->with('category')
                ->inCategory($categoryId)
                ->orderBy('service_category_id')
                ->ordered()
                ->paginate(20)
                ->withQueryString(),
            'categories' => ServiceCategory::ordered()->pluck('name', 'id'),
            'categoryId' => $categoryId,
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.services.create', [
            'service' => new Service(['is_active' => true, 'service_category_id' => $request->integer('category') ?: null]),
            'categories' => ServiceCategory::ordered()->pluck('name', 'id'),
        ]);
    }

    public function store(ServiceFormRequest $request): RedirectResponse
    {
        $service = Service::create($request->validated());

        return redirect()->route('admin.services.index', ['category' => $service->service_category_id])
            ->with('status', "Service \"{$service->name}\" created.");
    }

    public function edit(Service $service): View
    {
        return view('admin.services.edit', [
            'service' => $service,
            'categories' => ServiceCategory::ordered()->pluck('name', 'id'),
        ]);
    }

    public function update(ServiceFormRequest $request, Service $service): RedirectResponse
    {
        $service->update($request->validated());

        return redirect()->route('admin.services.index', ['category' => $service->service_category_id])
            ->with('status', "Service \"{$service->name}\" updated.");
    }

    public function destroy(Service $service): RedirectResponse
    {
        if (! $service->canBeDeleted()) {
            return back()->with('error', "Customers have already requested \"{$service->name}\", so it can't be deleted. Deactivate it instead.");
        }

        $service->delete();

        return back()->with('status', "Service \"{$service->name}\" deleted.");
    }
}
