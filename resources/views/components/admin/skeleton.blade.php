@props([
    'title',
    'text' => null,
])

<div class="sh-skeleton" {{ $attributes }}>
    <span class="sh-skeleton-line w-50"></span>
    <span class="sh-skeleton-line"></span>
    <span class="sh-skeleton-line w-75"></span>
    <span class="visually-hidden">{{ $title }}</span>
    @if ($text)<p class="mt-2 small text-muted mb-0">{{ $text }}</p>@endif
</div>
