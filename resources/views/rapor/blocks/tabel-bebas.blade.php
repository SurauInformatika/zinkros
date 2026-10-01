@php
    $cols = $props['columns'] ?? [];
    $rows = max(0, (int) ($props['rows'] ?? 0));
    $leftLabels = ['kegiatan', 'ekstrakurikuler', 'kegiatan ekstrakurikuler'];
@endphp
@if (! empty($props['title']))
    <h3 class="section">{{ $props['title'] }}</h3>
@endif
<table class="cells">
    <tr>
        @foreach ($cols as $idx => $col)
            <th class="{{ in_array(strtolower(trim((string) ($col['label'] ?? ''))), $leftLabels, true) ? 'left' : '' }}">{{ $col['label'] ?? '' }}</th>
        @endforeach
        @if (! empty($edit))
        <th class="cell-add"><button type="button" data-act="list-add" data-target="{{ $block['id'] }}" data-path="columns" class="addon-btn" title="Tambah kolom">+</button></th>
        @endif
    </tr>
    @for ($i = 0; $i < $rows; $i++)
        <tr>
            @foreach ($cols as $idx => $col)
                <td class="{{ in_array(strtolower(trim((string) ($col['label'] ?? ''))), $leftLabels, true) ? 'left' : '' }}">&nbsp;</td>
            @endforeach
            @if (! empty($edit))
            <td class="cell-add">&nbsp;</td>
            @endif
        </tr>
    @endfor
    @if (! empty($edit))
    <tr>
        <td colspan="{{ count($cols) + 1 }}" class="cell-add">
            <button type="button" data-act="prop-inc" data-target="{{ $block['id'] }}" data-path="rows" data-step="1" class="addon-btn">+ Tambah Baris</button>
            @if ($rows > 0)
            <button type="button" data-act="prop-inc" data-target="{{ $block['id'] }}" data-path="rows" data-step="-1" class="addon-btn">- Hapus Baris</button>
            @endif
        </td>
    </tr>
    @endif
</table>