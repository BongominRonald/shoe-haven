@props([
    'label',
    'subtitle' => null,
])

<div class="sh-section-title" {{ $attributes }}>
    <h3>{{ $label }}</h3>
    @if ($subtitle)<p>{{ $subtitle }}</p>@endif
</div>
