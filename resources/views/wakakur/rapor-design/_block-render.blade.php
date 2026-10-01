@php
    $bt = $block['type'] ?? '';
    $props = $block['props'] ?? [];
    $editAny = true;
@endphp
@if ($bt !== '' && View::exists('rapor.blocks.' . $bt))
    @include('rapor.blocks.' . $bt, ['block' => $block, 'props' => $props, 'ctx' => $ctx, 'edit' => $editAny, 'i' => $i])
@else
    <p class="text-xs text-slate-400 dark:text-white/30">Jenis blok tidak dikenal.</p>
@endif