@props([
    'type' => 'success',
    'title' => null,
    'message' => null,
])

<div class="sh-inline-alert sh-alert-{{ $type }}" role="alert" {{ $attributes }}>
    <div class="sh-alert-icon">
        <i class="bi {{ match ($type) {
            'success' => 'bi-check-circle',
            'info'    => 'bi-info-circle',
            'warning' => 'bi-exclamation-triangle',
            'danger'  => 'bi-x-octagon',
            default   => 'bi-info-circle',
        } }}"></i>
    </div>
    <div class="sh-alert-content">
        @if ($title)<strong>{{ $title }}</strong>@endif
        @if ($message || !$slot->isEmpty())<div>{{ $message ?? $slot }}</div>@endif
    </div>
</div>
