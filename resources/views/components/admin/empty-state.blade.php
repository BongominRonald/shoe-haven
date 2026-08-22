@props([
    'icon' => 'bi-inbox',
    'title' => 'Nothing here yet',
    'message' => null,
])

<div class="sh-empty-state" {{ $attributes }}>
    <div class="sh-empty-state-icon"><i class="bi {{ $icon }}"></i></div>
    <h4 class="sh-empty-state-title">{{ $title }}</h4>
    @if ($message)<p class="sh-empty-state-message">{{ $message }}</p>@endif
    @if (!$slot->isEmpty())
        <div class="sh-empty-state-action">{{ $slot }}</div>
    @endif
</div>
