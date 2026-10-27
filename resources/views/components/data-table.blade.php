@props(['columns' => [], 'rows' => collect(), 'emptyMessage' => 'Tidak ada data.'])

<div class="data-table-wrap panel section-panel">
    <table class="table-modern">
        <thead>
            <tr>
                @foreach($columns as $column)
                    <th>{{ $column }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    {{ $slot }}
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) }}">
                        <div class="empty-state">
                            <div>
                                <strong>Data tidak tersedia</strong>
                                <span>{{ $emptyMessage }}</span>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
