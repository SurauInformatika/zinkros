@php
    $lines = max(1, (int) ($props['lines'] ?? 3));
@endphp
@if (! empty($props['title']))
    <h3 class="section">{{ $props['title'] }}</h3>
@endif
<div class="catatan">
    @for ($i = 0; $i < $lines; $i++)
        <span class="garis">&nbsp;</span>
    @endfor
</div>