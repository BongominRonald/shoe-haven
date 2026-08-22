@props([
    'action' => '',
    'name' => 'search',
    'placeholder' => 'Search...',
    'value' => null,
    'buttonLabel' => 'Search',
])

<form action="{{ $action }}" method="GET" class="sh-search-form" role="search" {{ $attributes }}>
    <div class="sh-search-box">
        <i class="bi bi-search" aria-hidden="true"></i>
        <input
            type="search"
            name="{{ $name }}"
            value="{{ old($name, $value ?? request($name)) }}"
            placeholder="{{ $placeholder }}"
            aria-label="{{ $placeholder }}"
        >
        @if (request($name))
            <a href="{{ $action }}" class="sh-search-clear" aria-label="Clear search" title="Clear search"><i class="bi bi-x-lg"></i></a>
        @endif
    </div>
    @if ($buttonLabel)
        <button type="submit" class="sh-btn sh-btn-primary">{{ $buttonLabel }}</button>
    @endif
</form>
