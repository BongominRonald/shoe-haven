@props([
    'label' => 'Loading',
    'full' => false,
])

<div class="{{ $full ? 'sh-loading sh-loading-full' : 'sh-loading' }}" role="status" {{ $attributes }}>
    <span class="sh-spinner"></span>
    <span>{{ $label }}</span>
</div>
