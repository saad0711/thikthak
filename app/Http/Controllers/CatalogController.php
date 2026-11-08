<?php

namespace App\Http\Controllers;

use App\Http\Requests\CatalogSearchRequest;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

/**
 * F1: what customers (and guests) see of the catalog: categories, category pages and search.
 */
class CatalogController extends Controller
{
    public function index(): View
    {
        return view('catalog.index', [
            'categories' => $this->activeCategories()
                ->withCount(['services' => fn (Builder $query) => $query->where('is_active', true)])
                ->get(),
            'sorts' => Service::SORTS,
        ]);
    }

    public function search(CatalogSearchRequest $request): View
    {
        $filters = $request->validated();

        $services = Service::query()
            ->available()
            ->with('category')
            ->search($filters['q'] ?? null)
            ->inCategory(isset($filters['category']) ? (int) $filters['category'] : null)
            ->priceBetween($filters['min_price'] ?? null, $filters['max_price'] ?? null)
            ->sortedBy($filters['sort'] ?? null)
            ->paginate(12)
            ->withQueryString();

        return view('catalog.search', [
            'services' => $services,
            'filters' => $filters,
            'categories' => $this->activeCategories()->pluck('name', 'id'),
            'sorts' => Service::SORTS,
        ]);
    }

    public function category(ServiceCategory $category): View
    {
        abort_unless($category->is_active, 404);

        return view('catalog.category', [
            'category' => $category,
            'services' => $category->services()->active()->ordered()->get(),
        ]);
    }

    /**
     * @return Builder<ServiceCategory>
     */
    private function activeCategories(): Builder
    {
        return ServiceCategory::query()->active()->ordered();
    }
}
