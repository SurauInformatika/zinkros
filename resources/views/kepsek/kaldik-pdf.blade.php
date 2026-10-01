<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>KALDIK - {{ $kaldik->name }}</title>
    <style>
        @page { margin: 14mm 12mm 16mm 12mm; }
        * { padding: 0; box-sizing: border-box; }
        body { margin: 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #1a1a1a; line-height: 1.4; }

        .header { display: table; width: 100%; border-bottom: 2.5px solid #059669; padding-bottom: 8px; margin-bottom: 10px; }
        .header .logo-col { display: table-cell; width: 60px; vertical-align: middle; padding-right: 10px; }
        .header .logo-col img { width: 52px; height: 52px; object-fit: contain; }
        .header .head-col { display: table-cell; vertical-align: middle; text-align: center; }
        .header .school-name { font-size: 13px; font-weight: 800; color: #065f46; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.2; margin: 0; }
        .header .school-addr { font-size: 8.5px; color: #444; text-transform: uppercase; letter-spacing: 0.3px; margin: 1px 0 0; line-height: 1.3; }
        .header .doc-title { font-size: 11px; font-weight: 800; color: #111; text-transform: uppercase; letter-spacing: 1.5px; margin: 4px 0 0; }
        .header .doc-sub { font-size: 8.5px; color: #333; text-transform: uppercase; letter-spacing: 0.4px; margin: 1px 0 0; }

        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .meta-table td { padding: 4px 6px; vertical-align: top; }
        .meta-table .label { font-size: 8px; color: #666; text-transform: uppercase; letter-spacing: 0.4px; }
        .meta-table .value { font-size: 10px; font-weight: 600; }

        .status-badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 8px; font-weight: 700; text-transform: uppercase; }
        .status-draft { background: #f1f5f9; color: #64748b; }
        .status-pending { background: #fef3c7; color: #d97706; }
        .status-final { background: #d1fae5; color: #059669; }
        .status-archived { background: #e2e8f0; color: #94a3b8; }
        .version-pill { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 8px; font-weight: 700; background: #ecfdf5; color: #047857; border: 1px solid #bbf7d0; }

        h2 { font-size: 11px; font-weight: 700; color: #059669; border-left: 3px solid #059669; padding: 2px 0 2px 8px; margin: 18px 0 8px; background: #f0fdf4; page-break-after: avoid; }
        h3 { font-size: 10px; font-weight: 700; color: #374151; margin: 12px 0 6px; page-break-after: avoid; }

        .sem-block { font-size: 10.5px; font-weight: 700; color: #065f46; background: #ecfdf5; border-left: 3px solid #059669; padding: 4px 8px; margin: 16px 0 4px; page-break-after: avoid; }

        table.detail { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.detail th { background: #059669; color: #fff; font-size: 8px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; padding: 5px 8px; text-align: left; }
        table.detail td { padding: 5px 8px; font-size: 10px; border-bottom: 1px solid #e5e7eb; }
        table.detail tr:nth-child(even) td { background: #f9fafb; }

        table.month { width: 100%; border-collapse: collapse; }
        .month-pair { width: 100%; margin-bottom: 8px; page-break-inside: avoid; }
        .month-col { float: left; width: 33%; }
        .month-clear { clear: both; }
        .month-title { font-size: 8.5px; line-height: 1.3; color: #374151; font-weight: 700; margin: 6px 0 3px; }
        .month-title span { font-size: 7px; color: #6b7280; font-weight: 400; }
        table.month th { background: #065f46; color: #fff; font-size: 6.5px; font-weight: 700; text-transform: uppercase; padding: 2px 1px; text-align: center; }
        table.month td { border: 1px solid #d1d5db; width: 13.5%; height: 24px; padding: 1px 2px; vertical-align: top; }
        table.month td.empty { background: #f9fafb; }
        .dom { font-size: 8px; font-weight: 700; display: block; }
        .cell-dim { color: #cbd5e1; }
        .c-red { background: #fee2e2; }
        .c-green { background: #d1fae5; }
        .c-blue { background: #dbeafe; }
        .c-dim { background: #f9fafb; }

        .month-inner { display: table; width: 100%; table-layout: fixed; }
        .month-cal { display: table-cell; width: 56%; vertical-align: top; }
        .month-notes { display: table-cell; vertical-align: top; padding-left: 5px; }
        .notes-head { font-size: 7px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; color: #6b7280; margin: 2px 0 3px; }
        .notes-list { margin: 0; padding: 0; list-style: none; }
        .notes-list li { border-left: 2px solid #d1d5db; padding: 1px 0 2px 4px; margin-bottom: 3px; }
        .notes-list li.n-red { border-left-color: #ef4444; }
        .notes-list li.n-green { border-left-color: #047857; }
        .notes-list li.n-blue { border-left-color: #1d4ed8; }
        .notes-list .n-title { font-size: 7.5px; font-weight: 700; color: #374151; line-height: 1.25; }
        .notes-list .n-sub { font-size: 6.5px; color: #6b7280; line-height: 1.25; }
        .notes-empty { font-size: 7px; color: #9ca3af; font-style: italic; line-height: 1.3; }

        .no-data { font-size: 10px; color: #9ca3af; font-style: italic; padding: 6px 0; }
        .note { font-size: 8px; color: #6b7280; font-style: italic; margin: 4px 0 10px; }

        .footer { margin-top: 24px; border-top: 1px solid #e5e7eb; padding-top: 10px; }
        .footer .meta { font-size: 8px; color: #9ca3af; text-align: center; margin-top: 10px; }
        .approval-table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        .approval-table td { width: 50%; text-align: center; padding: 6px; vertical-align: bottom; }
        .approval-table .line { border-bottom: 1px solid #1a1a1a; margin: 34px 30px 4px; }
        .approval-table .name { font-weight: 700; font-size: 11px; }
        .approval-table .role { font-size: 9px; color: #666; }
    </style>
</head>
<body>
@php $doc = $doc ?? new \App\Support\KaldikDocument($kaldik); @endphp

<div class="header">
    <div class="logo-col">
        @php $logoUri = $doc->logoDataUri(); @endphp
        @if ($logoUri)
        <img src="{{ $logoUri }}" alt="Logo">
        @endif
    </div>
    <div class="head-col">
        <div class="school-name">{{ $kaldik->school->name ?? 'Sekolah' }}</div>
        <div class="school-addr">{{ $kaldik->school->address ?? '' }}</div>
        <div class="doc-title">Kalender Pendidikan (KALDIK)</div>
        <div class="doc-sub">{{ $kaldik->academicYear?->name ?? '' }} &middot; {{ $kaldik->semesterLabel() }}</div>
    </div>
</div>

<table class="meta-table">
    <tr>
        <td style="width:50%" colspan="2">
            <div class="label">Nama Dokumen</div>
            <div class="value">{{ $kaldik->name }}</div>
        </td>
        <td style="width:25%">
            <div class="label">Status</div>
            <div class="value"><span class="status-badge status-{{ $kaldik->status }}">{{ $doc->statusLabel() }}</span></div>
        </td>
        <td style="width:25%">
            <div class="label">Versi</div>
            <div class="value"><span class="version-pill">v{{ $kaldik->version }}</span>@if ($kaldik->isFinal() && $kaldik->parent_version_id) <span style="font-size:8px;color:#6b7280">(revisi)</span>@endif</div>
        </td>
    </tr>
    <tr>
        <td style="width:25%">
            <div class="label">Tahun Ajaran</div>
            <div class="value">{{ $kaldik->academicYear?->name ?? '-' }}</div>
        </td>
        <td style="width:25%">
            <div class="label">Cakupan</div>
            <div class="value">{{ $kaldik->semesterLabel() }} <span style="font-size:8px;color:#666">(1 tahun ajaran)</span></div>
        </td>
        <td style="width:25%">
            <div class="label">Sumber / Template</div>
            <div class="value">{{ $kaldik->template?->name ?? ($kaldik->source ?? '-') }}</div>
        </td>
        <td style="width:25%">
            <div class="label">Periode Berlaku</div>
            @if ($kaldik->hasExplicitSemesterBoundaries())
            <div class="value" style="font-size:9px">Semester 1: {{ $kaldik->start_date->format('d M Y') }} — {{ $kaldik->semester1EndDate()->format('d M Y') }}</div>
            <div class="value" style="font-size:9px">Semester 2: {{ $kaldik->semester_2_start_date->format('d M Y') }} — {{ $kaldik->end_date->format('d M Y') }}</div>
            @else
            <div class="value">{{ $kaldik->start_date->format('d M Y') }} — {{ $kaldik->end_date->format('d M Y') }}</div>
            @endif
        </td>
    </tr>
</table>

@foreach ($doc->semesterBlocks as $block)
<h3 class="sem-block">{{ $block['label'] }} &mdash; {{ $block['teachingDays'] }} hari efektif</h3>
@foreach (collect($block['months'])->chunk(3) as $pair)
<div class="month-pair">
    @foreach ($pair->values() as $month)
    <div class="month-col">
        <h3 class="month-title">{{ $month['label'] }} <span>({{ $month['teachingDays'] }} hari efektif)</span></h3>
        <div class="month-inner">
            <div class="month-cal">
                <table class="month">
                    <thead>
                        <tr>
                            @foreach ($doc->gridHeader as $gh)
                            <th>{{ $gh }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($month['rows'] as $week)
                        <tr>
                            @foreach ($week as $cell)
                            @if ($cell === null)
                            <td class="empty"></td>
                            @else
                            @php
                                $cc = $cell['classes'];
                                $color = in_array('holiday', $cc, true) || in_array('libur', $cc, true) ? 'c-red'
                                    : (in_array('exam', $cc, true) ? 'c-green'
                                    : (in_array('dim', $cc, true) ? 'c-dim' : 'c-blue'));
                            @endphp
                            <td class="cell {{ $color }}">
                                <span class="dom">@if (in_array('dim', $cc, true))<span class="cell-dim">{{ $cell['dom'] }}</span>@else{{ $cell['dom'] }}@endif</span>
                            </td>
                            @endif
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="month-notes">
                <div class="notes-head">Keterangan</div>
                @if (count($month['notes']) > 0)
                <ul class="notes-list">
                    @foreach ($month['notes'] as $note)
                    <li class="{{ $note['cls'] }}">
                        <div class="n-title">{{ $note['text'] }}</div>
                        <div class="n-sub">{{ $note['sub'] }}</div>
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="notes-empty">Tidak ada agenda di bulan ini</div>
                @endif
            </div>
        </div>
    </div>
    @endforeach
    <div class="month-clear"></div>
</div>
@endforeach
@endforeach

<div class="footer">
    @if ($kaldik->isFinal())
    <table class="approval-table">
        <tr>
            <td>
                <div class="line"></div>
                <div class="name">{{ $kaldik->creator?->name ?? '-' }}</div>
                <div class="role">Wakil Kepala Bidang Kurikulum</div>
            </td>
            <td>
                <div class="line"></div>
                <div class="name">{{ $kaldik->approver?->name ?? '-' }}</div>
                <div class="role">Kepala Sekolah</div>
            </td>
        </tr>
    </table>
    @else
    <div class="no-data">Dokumen belum final — bukan berkas resmi.</div>
    @endif
    <div class="meta">
        Dicetak pada {{ now()->format('d M Y H:i') }} &bull; Status {{ $doc->statusLabel() }} v{{ $kaldik->version }} &bull; {{ \App\Models\PlatformSetting::appName() }}
    </div>
</div>

</body>
</html>