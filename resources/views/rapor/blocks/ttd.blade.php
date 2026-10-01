@php
    $boxes = $props['boxes'] ?? [];
    $parent = $ctx['parent'] ?? null;
    $user = $ctx['user'] ?? auth()->user();
    $kepala = $ctx['kepalaSekolah'] ?? null;
    $nameFor = function ($key) use ($parent, $user, $kepala) {
        return match ($key) {
            'ortu' => $parent?->name,
            'wali' => $user?->name,
            'kepsek' => $kepala?->name,
            default => null,
        };
    };
@endphp
<div class="ttd">
    @foreach ($boxes as $box)
        <div class="blok">
            {{ $box['label'] ?? '' }}<br><br><br><br><b>{{ $nameFor($box['key'] ?? 'manual') ?? '........................................' }}</b>
        </div>
    @endforeach
</div>
@if (! empty($edit))
<p class="block-addon">
    <button type="button" data-act="list-add" data-target="{{ $block['id'] }}" data-path="boxes" class="addon-btn">+ Tambah Kotak TTD</button>
</p>
@endif