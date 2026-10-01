@php
    $subjects = $ctx['subjects'] ?? collect();
    $subjectGrades = $ctx['subjectGrades'] ?? collect();
    $gradeTypesAll = $ctx['gradeTypes'] ?? collect();
    $showNo = $props['show_no'] ?? true;
    $showKkm = $props['show_kkm'] ?? true;
    $showFinal = $props['show_final'] ?? true;
    $showPredikat = $props['show_predikat'] ?? true;
    $showKet = $props['show_keterangan'] ?? true;
    $typeIds = $props['grade_type_ids'] ?? [];
    $customCols = $props['custom_columns'] ?? [];
    $typeIds = is_array($typeIds) ? $typeIds : [];
    $gradeTypes = $typeIds !== [] ? $gradeTypesAll->whereIn('id', $typeIds)->values() : $gradeTypesAll->values();
    $colSpan = ($showNo ? 1 : 0) + 1 + ($showKkm ? 1 : 0) + $gradeTypes->count() + count($customCols) + ($showFinal ? 1 : 0) + ($showPredikat ? 1 : 0);
@endphp
@if (! empty($props['title']))
    <h3 class="section">{{ $props['title'] }}</h3>
@endif
<table class="cells">
    <thead>
        <tr>
            @if ($showNo) <th rowspan="2">No</th> @endif
            <th rowspan="2" class="left">Mata Pelajaran</th>
            @if ($showKkm) <th rowspan="2">KKM / Kriteria</th> @endif
            @foreach ($gradeTypes as $gt) <th>{{ $gt->name }}</th> @endforeach
            @foreach ($customCols as $c) <th rowspan="2">{{ $c['label'] ?? '' }}</th> @endforeach
            @if ($showFinal) <th rowspan="2">Nilai Akhir</th> @endif
            @if ($showPredikat) <th rowspan="2">Predikat</th> @endif
            @if (! empty($edit))
            <th rowspan="2" class="cell-add"><button type="button" data-act="list-add" data-target="{{ $block['id'] }}" data-path="custom_columns" class="addon-btn" title="Tambah kolom bebas">+</button></th>
            @endif
        </tr>
        <tr>
            @foreach ($gradeTypes as $gt) <th>({{ $gt->weight }}%)</th> @endforeach
        </tr>
    </thead>
    <tbody>
        @php $no = 1; @endphp
        @forelse ($subjects as $sub)
            @php $sg = $subjectGrades->get($sub->id); @endphp
            <tr>
                @if ($showNo) <td>{{ $no++ }}</td> @endif
                <td class="left">{{ $sub->name }}@if ($sub->type === 'QURAN') * @endif</td>
                @if ($showKkm) <td>{{ $sg['kkm'] ?? 80 }}</td> @endif
                @foreach ($gradeTypes as $gt)
                    <td>{{ ($sg['by_type'][$gt->id] ?? null) !== null ? $sg['by_type'][$gt->id] : '-' }}</td>
                @endforeach
                @foreach ($customCols as $c)
                    <td>&nbsp;</td>
                @endforeach
                @if ($showFinal) <td><b>{{ $sg['final'] ?? '-' }}</b></td> @endif
                @if ($showPredikat) <td><b>{{ $sg['predikat'] ?? '-' }}</b></td> @endif
            </tr>
        @empty
            <tr><td colspan="{{ $colSpan }}">Belum ada data nilai.</td></tr>
        @endforelse
    </tbody>
</table>
@if ($showKet)
<div class="keterangan">
    *) Mata pelajaran Al-Qur'an dilaporkan berdasarkan rekap tahfidz semester berjalan. &nbsp; Predikat: A (Sangat Baik 90–100), B (Baik 80–89), C (Cukup 70–79), D (Kurang 60–69), E (Sangat Kurang &lt; 60).
</div>
@endif
@if (! empty($edit))
<p class="block-addon">
    <button type="button" data-act="list-add" data-target="{{ $block['id'] }}" data-path="custom_columns" class="addon-btn">+ Tambah Kolom</button>
    @if ($showFinal)
    <button type="button" data-act="set" data-target="{{ $block['id'] }}" data-path="show_final" data-set="0" class="addon-btn">Sembunyikan Nilai Akhir</button>
    @endif
</p>
@endif