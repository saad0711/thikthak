@csrf

<x-select label="Category" name="service_category_id" :options="$categories" :value="$service->service_category_id" placeholder="Choose a category" required />
<x-input label="Name" name="name" :value="$service->name" maxlength="80" required />
<x-textarea label="Description (optional)" name="description" :value="$service->description" rows="3" />

<div class="grid gap-5 sm:grid-cols-3">
    <x-input label="Minimum price (৳)" name="price_min" type="number" min="0" step="1" :value="$service->price_min !== null ? (int) $service->price_min : null" required />
    <x-input label="Maximum price (৳)" name="price_max" type="number" min="0" step="1" :value="$service->price_max !== null ? (int) $service->price_max : null" required />
    <x-input label="Display order" name="sort_order" type="number" min="0" max="999" :value="$service->sort_order ?? 0" />
</div>
<p class="hint -mt-3">The price range is only a guide for customers. Providers set their own prices.</p>

<label class="flex items-center gap-2 text-sm">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="accent-teal-700" @checked(old('is_active', $service->is_active))>
    Active (customers can see and request this service)
</label>

<div class="flex gap-3">
    <button type="submit" class="btn-primary">Save service</button>
    <a href="{{ route('admin.services.index') }}" class="btn-secondary">Cancel</a>
</div>
