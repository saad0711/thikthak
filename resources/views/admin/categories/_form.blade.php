@csrf

<x-input label="Name" name="name" :value="$category->name" maxlength="60" required />
<x-textarea label="Description (optional)" name="description" :value="$category->description" rows="3" />

<div class="grid gap-5 sm:grid-cols-2">
    <x-input label="Icon (optional)" name="icon" :value="$category->icon" maxlength="10" hint="An emoji such as 🔧 works well." />
    <x-input label="Display order" name="sort_order" type="number" min="0" max="999" :value="$category->sort_order ?? 0" hint="Lower numbers appear first." />
</div>

<label class="flex items-center gap-2 text-sm">
    <input type="hidden" name="is_active" value="0">
    <input type="checkbox" name="is_active" value="1" class="accent-teal-700" @checked(old('is_active', $category->is_active))>
    Active (customers can see this category)
</label>

<div class="flex gap-3">
    <button type="submit" class="btn-primary">Save category</button>
    <a href="{{ route('admin.categories.index') }}" class="btn-secondary">Cancel</a>
</div>
