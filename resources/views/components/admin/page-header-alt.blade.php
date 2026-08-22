@props([
    'title',
    'subtitle' => null,
    'actions' => null,
    'tabs' => null,
])

<div class="sh-page-header" {{ $attributes }}>
    <div class="sh-page-header-text">
        <h2>{{ $title }}</h2>
        @if ($subtitle)<p>{{ $subtitle }}</p>@endif
    </div>
    @if ($actions)<div class="sh-page-header-actions">{{ $actions }}</div>@endif
</div>
