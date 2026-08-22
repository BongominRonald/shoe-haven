@props([
    'action',
    'message' => 'Delete this item?',
    'confirmText' => 'Delete',
    'label' => null,
    'icon' => 'bi-trash',
    'variant' => 'danger-light',
    'method' => 'DELETE',
])

@php($uid = 'del-' . md5($action . $message . (string) Str::random(6)))

<x-admin.button type="button" variant="{{ $variant }}" data-bs-toggle="modal" data-bs-target="#{{ $uid }}" {{ $attributes }}>
    @if ($icon)<i class="bi {{ $icon }} me-1"></i>@endif
    {{ $label ?? $slot }}
</x-admin.button>

<x-admin.confirm-modal
    :id="$uid"
    :title="$confirmText . '?'"
    :message="$message"
    :confirmText="$confirmText"
    variant="danger"
    :method="$method"
    :action="$action"
/>
