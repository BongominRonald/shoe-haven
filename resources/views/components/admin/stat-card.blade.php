@props([
    'title',
    'value',
    'icon' => null,
    'color' => 'primary',
    'trend' => null,
    'trendType' => null,
    'sub' => null,
    'href' => null,
])

@php
    $trendClass = match ($trendType) {
        'up' => 'sh-trend-up',
        'down' => 'sh-trend-down',
        default => 'sh-trend-flat',
    };
    $trendIcon = match ($trendType) {
        'up' => 'bi-arrow-up-right',
        'down' => 'bi-arrow-down-right',
        default => 'bi-dash',
    };
@endphp

<div class="sh-stat-card" {{ $attributes }}>
    @if ($icon)
        <div class="sh-stat-icon sh-stat-icon-{{ $color }}"><i class="bi {{ $icon }}"></i></div>
    @endif
    <div class="sh-stat-content">
        <div class="sh-stat-title">{{ $title }}</div>
        <div class="sh-stat-value">
            @if ($href)<a href="{{ $href }}">{{ $value }}</a>@else{{ $value }}@endif
        </div>
        @if ($trend)
            <div class="sh-stat-footer">
                <span class="sh-trend {{ $trendClass }}"><i class="bi {{ $trendIcon }}"></i>{{ $trend }}</span>
                @if ($sub)<span class="sh-stat-sub">{{ $sub }}</span>@endif
            </div>
        @elseif ($sub)
            <div class="sh-stat-footer"><span class="sh-stat-sub">{{ $sub }}</span></div>
        @endif
    </div>
</div>
