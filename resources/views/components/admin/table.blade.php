@props([
    'headers' => [],
    'rows' => [],
    'emptyMessage' => null,
    'emptyIcon' => 'bi-inbox',
    'emptyTitle' => 'Nothing here yet',
])

<div {{ $attributes->merge(['class' => 'sh-table-wrap']) }}>
    @if (count($rows) === 0)
        <x-admin.empty-state :icon="$emptyIcon" :title="$emptyTitle" :message="$emptyMessage" />
    @else
        <div class="table-responsive">
            <table class="sh-table">
                @if (count($headers) > 0)
                    <thead>
                        <tr>
                            @foreach ($headers as $header)
                                <th scope="col">{{ $header }}</th>
                            @endforeach
                        </tr>
                    </thead>
                @endif
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            @foreach ($row as $cell)
                                <td>{!! $cell !!}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
