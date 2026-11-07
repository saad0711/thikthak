@props([
    'label',
    'name',
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'bag' => 'default',
])

@php
    $id = $attributes->get('id', $name);
    $keepsValue = ! in_array($type, ['password', 'file'], true);
@endphp

<div>
    <label for="{{ $id }}" class="label">{{ $label }}</label>
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="{{ $type }}"
        @if ($keepsValue) value="{{ old($name, $value) }}" @endif
        {{ $attributes->except('id')->merge(['class' => $type === 'file' ? 'block w-full text-sm text-stone-600 file:mr-3 file:rounded-lg file:border-0 file:bg-teal-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-teal-800 hover:file:bg-teal-100' : 'input']) }}
    >
    @if ($hint)
        <p class="hint">{{ $hint }}</p>
    @endif
    @error($name, $bag)
        <p class="field-error">{{ $message }}</p>
    @enderror
</div>
