@extends('layouts.app')

@section('title', 'Categories')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="page-title">Service categories</h1>
        <a href="{{ route('admin.categories.create') }}" class="btn-primary">Add a category</a>
    </div>

    <div class="card mt-6 overflow-x-auto p-0">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-stone-200 bg-stone-50 text-xs uppercase tracking-wide text-stone-500">
                <tr>
                    <th class="px-4 py-3">Order</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Services</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($categories as $category)
                    <tr>
                        <td class="px-4 py-3 text-stone-500">{{ $category->sort_order }}</td>
                        <td class="px-4 py-3">
                            <span class="font-medium">{{ $category->icon }} {{ $category->name }}</span>
                            <span class="block text-xs text-stone-500">/categories/{{ $category->slug }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.services.index', ['category' => $category->id]) }}" class="link">{{ $category->services_count }}</a>
                        </td>
                        <td class="px-4 py-3">
                            @if ($category->is_active)
                                <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800">Active</span>
                            @else
                                <span class="rounded-full bg-stone-200 px-2.5 py-0.5 text-xs font-medium text-stone-700">Hidden</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn-secondary px-3 py-1.5">Edit</a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                      onsubmit="return confirm('Delete this category?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-secondary px-3 py-1.5 text-rose-700">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-stone-500">No categories yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
