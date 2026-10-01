@php
    $stats = $ctx['tahfidzStats'] ?? null;
    $surah = collect($stats['surah_names'] ?? []);
@endphp
@if ($stats)
    @if (! empty($props['title']))
        <h3 class="section">{{ $props['title'] }}</h3>
    @endif
    <table class="cells">
        <tr><th>Total Setoran</th><th>Total Ayat</th><th>Rata-rata Skor</th><th>Predikat</th></tr>
        <tr>
            <td>{{ $stats['total'] }}</td>
            <td>{{ $stats['total_ayat'] }} ayat</td>
            <td>{{ $stats['avg_score'] }}</td>
            <td>{{ \App\Models\TahfidzRecord::scoreToPredikat((int) $stats['avg_score']) }}</td>
        </tr>
    </table>
    @if (($props['show_surah'] ?? true) && $surah->isNotEmpty())
        <div class="catatan">
            <b>Surah yang dihafalkan:</b> {{ $surah->implode(', ') }}
        </div>
    @endif
@endif