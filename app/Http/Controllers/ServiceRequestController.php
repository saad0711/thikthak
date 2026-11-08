<?php

namespace App\Http\Controllers;

use App\Enums\ServiceRequestStatus;
use App\Http\Requests\ServiceRequests\CancelServiceRequestRequest;
use App\Http\Requests\ServiceRequests\StoreServiceRequestRequest;
use App\Http\Requests\ServiceRequests\UpdateServiceRequestRequest;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * F4: customers post, view, edit and cancel their service requests.
 * Routes are limited to customers by the `role:customer` middleware.
 */
class ServiceRequestController extends Controller
{
    public function index(Request $request): View
    {
        $status = ServiceRequestStatus::tryFrom((string) $request->query('status'));

        return view('requests.index', [
            'serviceRequests' => $request->user()->serviceRequests()
                ->with('service.category')
                ->withStatus($status)
                ->latest()
                ->paginate(10)
                ->withQueryString(),
            'currentStatus' => $status,
        ]);
    }

    public function create(Request $request): View
    {
        return view('requests.create', [
            'serviceRequest' => new ServiceRequest([
                'service_id' => $request->integer('service') ?: null,
                'area_name' => $request->user()->area,
            ]),
            'servicesByCategory' => $this->servicesByCategory(),
            'areas' => array_keys(config('thikthak.areas')),
        ]);
    }

    public function store(StoreServiceRequestRequest $request): RedirectResponse
    {
        $serviceRequest = DB::transaction(function () use ($request) {
            $serviceRequest = $request->user()->serviceRequests()->create($request->safe()->except('photos'));
            $this->storePhotos($serviceRequest, $request->file('photos', []));

            return $serviceRequest;
        });

        return redirect()->route('requests.show', $serviceRequest)
            ->with('status', 'Your request is posted. Providers who offer this service can now see it.');
    }

    public function show(ServiceRequest $serviceRequest): View
    {
        Gate::authorize('view', $serviceRequest);

        return view('requests.show', [
            'serviceRequest' => $serviceRequest->load(['service.category', 'photos' => $this->photoOrder()]),
        ]);
    }

    public function edit(ServiceRequest $serviceRequest): View
    {
        Gate::authorize('update', $serviceRequest);

        return view('requests.edit', [
            'serviceRequest' => $serviceRequest->load(['photos' => $this->photoOrder()]),
            'servicesByCategory' => $this->servicesByCategory(),
            'areas' => array_keys(config('thikthak.areas')),
        ]);
    }

    public function update(UpdateServiceRequestRequest $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        DB::transaction(function () use ($request, $serviceRequest) {
            $serviceRequest->update($request->safe()->except(['photos', 'remove_photos']));

            // Deleting each model (not a mass delete) also removes the image file.
            $serviceRequest->photos()->whereIn('id', $request->input('remove_photos', []))->get()->each->delete();

            $this->storePhotos($serviceRequest, $request->file('photos', []));
        });

        return redirect()->route('requests.show', $serviceRequest)->with('status', 'Your request has been updated.');
    }

    public function cancel(CancelServiceRequestRequest $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        $serviceRequest->cancel($request->validated('cancel_reason'));

        return redirect()->route('requests.show', $serviceRequest)->with('status', 'Your request has been cancelled.');
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    private function storePhotos(ServiceRequest $serviceRequest, array $files): void
    {
        $next = (int) $serviceRequest->photos()->max('sort_order') + 1;

        foreach ($files as $file) {
            $serviceRequest->photos()->create([
                'path' => $file->store("service-requests/{$serviceRequest->id}", 'public'),
                'sort_order' => $next++,
            ]);
        }
    }

    private function photoOrder(): Closure
    {
        return fn ($query) => $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Active services grouped by their (active) category, for the service dropdown.
     *
     * @return array<string, array<int, string>>
     */
    private function servicesByCategory(): array
    {
        return ServiceCategory::query()
            ->active()
            ->ordered()
            ->with(['services' => fn ($query) => $query->active()->ordered()])
            ->get()
            ->filter(fn (ServiceCategory $category) => $category->services->isNotEmpty())
            ->mapWithKeys(fn (ServiceCategory $category) => [$category->name => $category->services->pluck('name', 'id')->all()])
            ->all();
    }
}
