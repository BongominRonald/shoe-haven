@props([
    'submit' => 'Save changes',
    'cancel' => null,
    'submitIcon' => 'bi-check-lg',
])

<div class="sh-form-actions" {{ $attributes }}>
    @if ($cancel)
        <a href="{{ $cancel }}" class="sh-btn sh-btn-light">Cancel</a>
    @endif
    <button type="submit" class="sh-btn sh-btn-primary" data-loading>
        @if ($submitIcon)<i class="bi {{ $submitIcon }} me-2"></i>@endif
        {{ $submit }}
    </button>
</div>
