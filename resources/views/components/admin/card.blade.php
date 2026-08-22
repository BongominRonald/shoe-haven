@props([
    'title' => null,
    'subtitle' => null,
    'actions' => null,
    'padding' => true,
])

<div {{ $attributes->merge(['class' => 'sh-card']) }}>
    @if ($title || $subtitle || $actions)
        <div class="sh-card-header">
            <div>
                @if ($title)<h3 class="sh-card-title">{{ $title }}</h3>@endif
                @if ($subtitle)<p class="sh-card-subtitle">{{ $subtitle }}</p>@endif
            </div>
            @if ($actions)<div class="sh-card-actions">{{ $actions }}</div>@endif
        </div>
    @endif
    <div class="{{ $padding ? 'sh-card-body' : '' }}">
        {{ $slot }}
    </div>
</div>
