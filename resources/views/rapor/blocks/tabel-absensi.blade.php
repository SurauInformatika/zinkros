@php
    $att = $ctx['attStats'] ?? [];
@endphp
@if (! empty($props['title']))
    <h3 class="section">{{ $props['title'] }}</h3>
@endif
<table class="cells">
    <tr><th>Sakit (S)</th><th>Izin (I)</th><th>Tanpa Keterangan (A)</th></tr>
    <tr>
        <td>{{ $att['sakit'] ?? 0 }} hari</td>
        <td>{{ $att['izin'] ?? 0 }} hari</td>
        <td>{{ $att['alpa'] ?? 0 }} hari</td>
    </tr>
</table>