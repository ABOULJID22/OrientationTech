@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-[#2563eb] dark:text-white']) }}>
    {{ $value ?? $slot }}
</label>
