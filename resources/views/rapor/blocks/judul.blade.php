@php
    $showSemester = $props['show_semester'] ?? true;
    $semester = $ctx['semester'] ?? 1;
    $ay = $ctx['ay'] ?? null;
    $baseSize = max(8, min(72, (int) ($props['font_size'] ?? 16)));
    $semSize = max(8, min(40, (int) ($props['semester_size'] ?? 12)));
    $lines = is_array($props['lines'] ?? null) && count($props['lines']) > 0
        ? array_values(array_filter($props['lines'], fn ($l) => $l !== null))
        : [];
    $titleLines = $lines;
@endphp
<div class="judul">
    @if (! empty($edit))
    <input type="hidden" data-b="{{ $i }}" data-path="props.font_size" value="{{ $baseSize }}">
    <input type="hidden" data-b="{{ $i }}" data-path="props.semester_size" value="{{ $semSize }}">
    @endif

    @foreach ($titleLines as $li => $line)
        @php $lineSize = max(8, min(72, (int) ($props['line_sizes'][$li] ?? $baseSize))); @endphp
        <div class="kop-title-line judul-line" data-line-index="{{ $li }}" data-size="{{ $lineSize }}">
            @if (! empty($edit))
            <input type="hidden" data-b="{{ $i }}" data-path="props.lines.{{ $li }}" value="{{ $line }}">
            <input type="hidden" data-b="{{ $i }}" data-path="props.line_sizes.{{ $li }}" value="{{ $lineSize }}">
            @endif
            <span class="kop-title-text judul-text" style="font-size: {{ $lineSize }}px;"
                @if (! empty($edit)) contenteditable="true" spellcheck="false" data-edit-title="{{ $block['id'] }}" data-edit-idx="{{ $li }}" @endif>{{ $line }}</span>
            @if (! empty($edit))
            <span class="kop-title-ctls">
                <button type="button" data-act="title-move" data-target="{{ $block['id'] }}" data-idx="{{ $li }}" data-dir="up" title="Naikkan baris">&uarr;</button>
                <button type="button" data-act="title-move" data-target="{{ $block['id'] }}" data-idx="{{ $li }}" data-dir="down" title="Turunkan baris">&darr;</button>
                <span class="kop-title-size">
                    <button type="button" data-act="title-size" data-target="{{ $block['id'] }}" data-idx="{{ $li }}" data-dir="down" title="Perkecil ukuran baris ini">&ndash;A</button>
                    <b>{{ $lineSize }}px</b>
                    <button type="button" data-act="title-size" data-target="{{ $block['id'] }}" data-idx="{{ $li }}" data-dir="up" title="Perbesar ukuran baris ini">A+</button>
                </span>
                <button type="button" class="kop-title-del" data-act="title-del" data-target="{{ $block['id'] }}" data-idx="{{ $li }}" title="Hapus baris judul">&times;</button>
            </span>
            @endif
        </div>
    @endforeach

    @if ($showSemester)
        @php
            $semText = (string) ($props['semester_text'] ?? '');
            $tahunDisplay = filled($semText)
                ? $semText
                : 'SEMESTER '.($semester === 1 ? 'GANJIL' : 'GENAP').' &middot; TAHUN PELAJARAN '.($ay?->name ?? '.................');
        @endphp
        <div class="kop-title-line judul-line tahun" data-line-index="tahun" data-size="{{ $semSize }}">
            @if (! empty($edit))
            <input type="hidden" data-b="{{ $i }}" data-path="props.semester_text" value="{{ $semText }}">
            @endif
            <span class="tahun-text" style="font-size: {{ $semSize }}px;"
                @if (! empty($edit)) contenteditable="true" spellcheck="false" data-edit-title="{{ $block['id'] }}" data-edit-key="semester_text" @endif>{!! $tahunDisplay !!}</span>
            @if (! empty($edit))
            <span class="kop-title-ctls">
                <span class="kop-title-size">
                    <button type="button" data-act="prop-inc" data-target="{{ $block['id'] }}" data-path="semester_size" data-step="-1" title="Perkecil ukuran baris semester">&ndash;A</button>
                    <b>{{ $semSize }}px</b>
                    <button type="button" data-act="prop-inc" data-target="{{ $block['id'] }}" data-path="semester_size" data-step="1" title="Perbesar ukuran baris semester">A+</button>
                </span>
                <button type="button" class="kop-title-del" data-act="set" data-target="{{ $block['id'] }}" data-path="show_semester" data-set="0" title="Hapus semester &amp; tahun pelajaran">&times;</button>
            </span>
            @endif
        </div>
    @endif

    @if (! empty($edit))
    <div class="kop-title-add-row">
        <button type="button" data-act="title-add" data-target="{{ $block['id'] }}" class="addon-btn">+ Baris Judul</button>
    </div>
    @endif
</div>