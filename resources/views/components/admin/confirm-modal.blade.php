@props([
    'id',
    'title' => 'Are you sure?',
    'message' => 'This action cannot be undone.',
    'confirmText' => 'Confirm',
    'confirmVariant' => 'danger',
    'method' => 'POST',
    'action' => '',
    'confirmIcon' => null,
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content sh-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $id }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{ $message }}
            </div>
            <div class="modal-footer">
                <x-admin.button type="button" variant="light" data-bs-dismiss="modal">Cancel</x-admin.button>
                <form action="{{ $action }}" method="POST" class="d-inline" data-loading>
                    @csrf
                    @method($method)
                    <x-admin.button type="submit" variant="{{ $confirmVariant }}" icon="{{ $confirmIcon }}">
                        {{ $confirmText }}
                    </x-admin.button>
                </form>
            </div>
        </div>
    </div>
</div>
