@php
    $typeLabels = \App\Support\RaporFormat::labels();
@endphp
@foreach ($blocks as $i => $block)
        @php
            $bid = $block['id'] ?? ('b' . $i);
            $bt = $block['type'] ?? '';
            $typeLabel = $typeLabels[$bt] ?? $bt;
            $isJudul = $bt === 'judul';
            $linesField = $isJudul ? 'lines' : 'title_lines';
            $sizesField = $isJudul ? 'line_sizes' : 'title_sizes';
        @endphp
        <div class="block-shell" data-block-shell="{{ $i }}" data-block-id="{{ $bid }}" data-title-lines="{{ $linesField }}" data-title-sizes="{{ $sizesField }}">
            <input type="hidden" data-b="{{ $i }}" data-path="id" value="{{ $bid }}">
            <input type="hidden" data-b="{{ $i }}" data-path="type" value="{{ $bt }}">

            <div class="block-bar">
                <span class="block-bar-label">{{ $typeLabel }}</span>
                <span class="block-bar-btns">
                    <button type="button" data-act="move" data-target="{{ $bid }}" data-dir="up" title="Naikkan blok">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
                    </button>
                    <button type="button" data-act="move" data-target="{{ $bid }}" data-dir="down" title="Turunkan blok">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <button type="button" data-act="properties" data-target="{{ $bid }}" title="Edit properti blok">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </button>
                    <button type="button" data-act="delete" data-target="{{ $bid }}" title="Hapus blok" class="text-red-400 hover:!text-red-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </span>
            </div>

            <div class="block-render" data-render-slot="{{ $i }}">
                @include('wakakur.rapor-design._block-render', ['block' => $block, 'i' => $i, 'ctx' => $ctx])
            </div>

            <div class="block-props" data-props-panel style="display:none">
                @include('wakakur.rapor-design._block-editor', ['i' => $i, 'block' => $block, 'gradeTypes' => $gradeTypes ?? collect()])
            </div>
        </div>
    @endforeach

    <div class="add-block-bar">
        <span class="text-xs font-semibold text-slate-500 dark:text-white/40">Tambah Blok</span>
        <select id="add-type" class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white">
            @foreach ($typeLabels as $type => $label)
            <option value="{{ $type }}">{{ $label }}</option>
            @endforeach
        </select>
        <button type="button" data-act="add"
            class="inline-flex items-center gap-1 rounded-lg border border-primary/30 bg-primary/5 hover:bg-primary/10 px-3 py-2 text-sm font-semibold text-primary transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Blok
        </button>
    </div>