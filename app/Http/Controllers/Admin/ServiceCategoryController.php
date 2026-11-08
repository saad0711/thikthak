<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryFormRequest;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * F1: admin management of catalog categories.
 */
class ServiceCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => ServiceCategory::query()->withCount('services')->ordered()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.create', ['category' => new ServiceCategory(['is_active' => true])]);
    }

    public function store(CategoryFormRequest $request): RedirectResponse
    {
        $category = ServiceCategory::create($request->validated());

        return redirect()->route('admin.categories.index')->with('status', "Category \"{$category->name}\" created.");
    }

    public function edit(ServiceCategory $category): View
    {
        return view('admin.categories.edit', ['category' => $category]);
    }

    public function update(CategoryFormRequest $request, ServiceCategory $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('admin.categories.index')->with('status', "Category \"{$category->name}\" updated.");
    }

    public function destroy(ServiceCategory $category): RedirectResponse
    {
        if (! $category->canBeDeleted()) {
            return back()->with('error', "\"{$category->name}\" still has services, so it can't be deleted. Deactivate it instead to hide it from customers.");
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', "Category \"{$category->name}\" deleted.");
    }
}
