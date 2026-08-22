@props([
    'icon',
    'color' => 'primary',
])

<span class="sh-stat-icon sh-stat-icon-{{ $color }}" {{ $attributes }}>
    <i class="bi {{ $icon }}"></i>
</span>
