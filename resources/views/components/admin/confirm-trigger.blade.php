@props([
    'title' => 'Confirm action',
    'message' => 'Are you sure you want to continue?',
    'confirmText' => 'Confirm',
    'confirmIcon' => 'bi-check-lg',
    'variant' => 'primary',
    'formId' => null,
])

<button type="button"
        class="sh-btn sh-btn-{{ $variant }}"
        data-bs-toggle="modal"
        data-bs-target="#{{ $formId ?? '' }}"
        {{ $attributes }}>
    {{ $slot }}
</button>
