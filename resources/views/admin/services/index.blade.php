@extends('layouts.app')

@section('title', 'Services')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="page-title">Services</h1>
        <a href="{{ route('admin.services.create', ['category' => $categoryId]) }}" class="btn-primary">Add a service</a>
    </div>

    <form method="GET" action="{{ route('admin.services.index') }}" class="mt-6 flex flex-wrap items-end gap-3">
        <div class="w-64">
            <x-select label="Category" name="category" :options="$categories" :value="$categoryId" placeholder="All categories" />
        </div>
        <button type="submit" class="btn-secondary">Filter</button>
    </form>

    <div class="card mt-6 overflow-x-auto p-0">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-stone-200 bg-stone-50 text-xs uppercase tracking-wide text-stone-500">
                <tr>
                    <th class="px-4 py-3">Service</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Price range</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($services as $service)
                    <tr>
                        <td class="px-4 py-3">
                            <span class="font-medium">{{ $service->name }}</span>
                            <span class="block text-xs text-stone-500">Order {{ $service->sort_order }}</span>
                        </td>
                        <td class="px-4 py-3">{{ $service->category->name }}</td>
                        <td class="px-4 py-3">{{ $service->priceRangeLabel() }}</td>
                        <td class="px-4 py-3">
                            @if ($service->is_active)
                                <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">Active</span>
                            @else
                                <span class="rounded-full bg-stone-200 px-2.5 py-0.5 text-xs font-medium text-stone-700">Hidden</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.services.edit', $service) }}" class="btn-secondary px-3 py-1.5">Edit</a>
                                <form method="POST" action="{{ route('admin.services.destroy', $service) }}"
                                      onsubmit="return confirm('Delete this service?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-secondary px-3 py-1.5 text-rose-700">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-stone-500">No services here yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $services->links() }}</div>
@endsection
