@php
    $blocks = $blocks ?? [];
    $ctx = $ctx ?? [];
@endphp
@foreach ($blocks as $block)
    @php
        $bt = $block['type'] ?? '';
        $props = $block['props'] ?? [];
    @endphp
    @if ($bt !== '' && View::exists('rapor.blocks.' . $bt))
        @include('rapor.blocks.' . $bt, ['block' => $block, 'props' => $props, 'ctx' => $ctx])
    @endif
@endforeach