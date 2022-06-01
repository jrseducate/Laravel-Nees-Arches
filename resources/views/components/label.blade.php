@props(['value'])

<label {{ $attributes->merge(['class' => 'na-label block font-medium text-sm text-gray-700']) }}>
    {{ $value ?? $slot }}
</label>
