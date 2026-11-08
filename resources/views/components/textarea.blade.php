@props([
    'label',
    'name',
    'value' => null,
    'rows' => 4,
    'hint' => null,
])

<div>
    <label for="{{ $name }}" class="label">{{ $label }}</label>
    <textarea id="{{ $name }}" name="{{ $name }}" rows="{{ $rows }}" {{ $attributes->merge(['class' => 'input']) }}>{{ old($name, $value) }}</textarea>
    @if ($hint)
        <p class="hint">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="field-error">{{ $message }}</p>
    @enderror
</div>
