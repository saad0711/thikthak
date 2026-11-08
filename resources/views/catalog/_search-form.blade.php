{{-- Shared search and filter form for the catalog (F1). --}}
<form method="GET" action="{{ route('catalog.search') }}" class="card grid gap-4 p-4 md:grid-cols-12 md:items-end">
    <div class="md:col-span-4">
        <x-input label="What do you need?" name="q" :value="$filters['q'] ?? null" placeholder="e.g. AC servicing, plumber" maxlength="100" />
    </div>
    <div class="md:col-span-2">
        <x-select label="Category" name="category" :options="$categories" :value="$filters['category'] ?? null" placeholder="All" />
    </div>
    <div class="md:col-span-2">
        <x-input label="Min price (৳)" name="min_price" type="number" min="0" :value="$filters['min_price'] ?? null" />
    </div>
    <div class="md:col-span-2">
        <x-input label="Max price (৳)" name="max_price" type="number" min="0" :value="$filters['max_price'] ?? null" />
    </div>
    <div class="md:col-span-2">
        <x-select label="Sort by" name="sort" :options="$sorts" :value="$filters['sort'] ?? 'name_asc'" />
    </div>
    <div class="flex gap-3 md:col-span-12">
        <button type="submit" class="btn-primary">Search</button>
        <a href="{{ route('catalog.search') }}" class="btn-secondary">Clear filters</a>
    </div>
</form>
