@props([
    'label',
    'name',
    'options' => [],
    'value' => null,
    'placeholder' => null,
    'hint' => null,
])

<div>
    <label for="{{ $name }}" class="label">{{ $label }}</label>
    <select id="{{ $name }}" name="{{ $name }}" {{ $attributes->merge(['class' => 'input']) }}>
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected((string) old($name, $value) === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @if ($hint)
        <p class="hint">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="field-error">{{ $message }}</p>
    @enderror
</div>
