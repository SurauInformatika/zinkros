@php
    $student = $ctx['student'] ?? null;
    $school = $ctx['school'] ?? null;
    $semester = $ctx['semester'] ?? 1;
    $ay = $ctx['ay'] ?? null;
    $left = array_values(array_filter($props['left'] ?? [], 'is_array'));
    $right = array_values(array_filter($props['right'] ?? [], 'is_array'));
    $value = function ($key) use ($student, $school, $semester, $ay) {
        return match ($key) {
            'name' => $student?->name ?? '',
            'nisn_nis' => ($student?->nisn ?? '-') . ' / ' . ($student?->nis ?? '-'),
            'gender' => $student ? ($student->gender === 'P' ? 'Perempuan' : 'Laki-laki') : '',
            'school_name' => $school?->name ?? '-',
            'class_semester' => ($student?->classRoom?->class_name ?? '-') . ' / ' . ($semester === 1 ? '1 (Ganjil)' : '2 (Genap)'),
            'tahun' => $ay?->name ?? '.................',
            default => '',
        };
    };
@endphp
<table class="id id-table" data-block-target="{{ $block['id'] }}">
    <tr>
        <td class="id-col" id="id-left">
            @foreach ($left as $li => $row)
                <div class="id-row">
                    @if (! empty($edit))
                    <span class="id-handle" data-drag-handle title="Geser untuk memindahkan baris">&vellip;</span>
                    @endif
                    <span class="lbl"@if (! empty($edit)) contenteditable="true" spellcheck="false" data-edit-label="{{ $block['id'] }}" data-label-path="props.left.{{ $li }}.label" @endif>{{ $row['label'] ?? '' }}</span>
                    <span class="id-val">:&nbsp;<span class="dotted">&nbsp;&nbsp;{{ $value($row['key'] ?? 'none') }}</span></span>
                    @if (! empty($edit))
                    <span class="kop-title-ctls id-ctls">
                        <button type="button" data-act="list-move" data-target="{{ $block['id'] }}" data-path="left" data-idx="{{ $li }}" data-dir="up" title="Naikkan baris">&uarr;</button>
                        <button type="button" data-act="list-move" data-target="{{ $block['id'] }}" data-path="left" data-idx="{{ $li }}" data-dir="down" title="Turunkan baris">&darr;</button>
                        <button type="button" data-act="col-move" data-target="{{ $block['id'] }}" data-path="left" data-idx="{{ $li }}" title="Pindah ke kolom kanan">&harr;</button>
                    </span>
                    @endif
                </div>
            @endforeach
            @if (! empty($edit))
            <p class="block-addon">
                <button type="button" data-act="list-add" data-target="{{ $block['id'] }}" data-path="left" class="addon-btn">+ Baris Kiri</button>
            </p>
            @endif
        </td>
        <td class="id-col" id="id-right">
            @foreach ($right as $li => $row)
                <div class="id-row">
                    @if (! empty($edit))
                    <span class="id-handle" data-drag-handle title="Geser untuk memindahkan baris">&vellip;</span>
                    @endif
                    <span class="lbl"@if (! empty($edit)) contenteditable="true" spellcheck="false" data-edit-label="{{ $block['id'] }}" data-label-path="props.right.{{ $li }}.label" @endif>{{ $row['label'] ?? '' }}</span>
                    <span class="id-val">:&nbsp;<span class="dotted">&nbsp;&nbsp;{{ $value($row['key'] ?? 'none') }}</span></span>
                    @if (! empty($edit))
                    <span class="kop-title-ctls id-ctls">
                        <button type="button" data-act="list-move" data-target="{{ $block['id'] }}" data-path="right" data-idx="{{ $li }}" data-dir="up" title="Naikkan baris">&uarr;</button>
                        <button type="button" data-act="list-move" data-target="{{ $block['id'] }}" data-path="right" data-idx="{{ $li }}" data-dir="down" title="Turunkan baris">&darr;</button>
                        <button type="button" data-act="col-move" data-target="{{ $block['id'] }}" data-path="right" data-idx="{{ $li }}" title="Pindah ke kolom kiri">&harr;</button>
                    </span>
                    @endif
                </div>
            @endforeach
            @if (! empty($edit))
            <p class="block-addon">
                <button type="button" data-act="list-add" data-target="{{ $block['id'] }}" data-path="right" class="addon-btn">+ Baris Kanan</button>
            </p>
            @endif
        </td>
    </tr>
</table>