@php
    $school = $ctx['school'] ?? null;
    $showLogo = $props['show_logo'] ?? true;
    $showAddress = $props['show_address'] ?? true;
    $showContact = $props['show_contact'] ?? true;
    $nama = $school?->name ?? \App\Models\PlatformSetting::appName();
    $hasCustomTitle = is_array($props['title_lines'] ?? null) && count($props['title_lines']) > 0;
    $titleLines = $hasCustomTitle
        ? array_values(array_filter($props['title_lines'], fn ($l) => $l !== null))
        : array_values(array_filter([$nama, filled($school?->address) ? $school->address : null]));
    $titleSize = max(8, min(40, (int) ($props['title_font_size'] ?? 15)));
    $logoPath = $props['logo'] ?? '';
    $schoolLogo = $school?->logo ? asset('storage/'.$school->logo) : null;
    $globalLogo = \App\Models\PlatformSetting::logo() ? asset('storage/'.\App\Models\PlatformSetting::logo()) : null;
    $logo = $logoPath ? asset('storage/'.$logoPath) : ($schoolLogo ?: $globalLogo);
@endphp
<div class="kop">
    @if (! empty($edit))
    <input type="hidden" data-b="{{ $i }}" data-path="props.logo" value="{{ $logoPath }}">
    @endif

    <div class="kop-inner">
        @if ($showLogo)
            <div class="kop-logo-editor"
                @if (! empty($edit)) data-logo-drop="{{ $block['id'] }}" @endif>
                @if ($logo)
                    <img src="{{ $logo }}" alt="Logo" class="kop-logo">
                @else
                    <div class="kop-logo-placeholder">Logo</div>
                @endif
                @if (! empty($edit))
                    <div class="kop-logo-overlay">
                        <span>Ganti Logo</span>
                        <small>drag &amp; drop gambar</small>
                    </div>
                    @if ($logoPath)
                    <button type="button" class="kop-logo-remove" data-logo-remove="{{ $block['id'] }}" title="Hapus logo">&times;</button>
                    @endif
                @endif
            </div>
        @endif

        <div class="kop-text">
            <div class="nama" style="font-size: {{ $titleSize }}px;">
                @foreach ($titleLines as $li => $line)
                    @php $lineSize = max(8, min(72, (int) ($props['title_sizes'][$li] ?? $titleSize))); @endphp
                    <div class="kop-title-line" data-line-index="{{ $li }}" data-size="{{ $lineSize }}" @if (! $hasCustomTitle) data-virtual="1" @endif>
                        @if (! empty($edit) && $hasCustomTitle)
                        <input type="hidden" data-b="{{ $i }}" data-path="props.title_lines.{{ $li }}" value="{{ $line }}">
                        <input type="hidden" data-b="{{ $i }}" data-path="props.title_sizes.{{ $li }}" value="{{ $lineSize }}">
                        @endif
                        <span class="kop-title-text" style="font-size: {{ $lineSize }}px;"
                            @if (! empty($edit)) contenteditable="true" spellcheck="false" data-edit-title="{{ $block['id'] }}" data-edit-idx="{{ $li }}" @endif>{{ $line }}</span>
                        @if (! empty($edit))
                        <span class="kop-title-ctls">
                            @if ($hasCustomTitle)
                            <button type="button" data-act="title-move" data-target="{{ $block['id'] }}" data-idx="{{ $li }}" data-dir="up" title="Naikkan baris">&uarr;</button>
                            <button type="button" data-act="title-move" data-target="{{ $block['id'] }}" data-idx="{{ $li }}" data-dir="down" title="Turunkan baris">&darr;</button>
                            @endif
                            <span class="kop-title-size">
                                <button type="button" data-act="title-size" data-target="{{ $block['id'] }}" data-idx="{{ $li }}" data-dir="down" title="Perkecil ukuran baris ini">&ndash;A</button>
                                <b>{{ $lineSize }}px</b>
                                <button type="button" data-act="title-size" data-target="{{ $block['id'] }}" data-idx="{{ $li }}" data-dir="up" title="Perbesar ukuran baris ini">A+</button>
                            </span>
                            @if ($hasCustomTitle)
                            <button type="button" class="kop-title-del" data-act="title-del" data-target="{{ $block['id'] }}" data-idx="{{ $li }}" title="Hapus baris judul">&times;</button>
                            @endif
                        </span>
                        @endif
                    </div>
                @endforeach
            </div>

            @if (! empty($edit))
            <div class="kop-title-add-row">
                <button type="button" data-act="title-add" data-target="{{ $block['id'] }}" class="addon-btn">+ Judul Kop</button>
                <span class="kop-fontctl">
                    <span class="kop-fontctl-label">Semua baris (default)</span>
                    <button type="button" data-act="prop-inc" data-target="{{ $block['id'] }}" data-path="title_font_size" data-step="-1" title="Perkecil font semua baris">&ndash;A</button>
                    <b>{{ $titleSize }}px</b>
                    <button type="button" data-act="prop-inc" data-target="{{ $block['id'] }}" data-path="title_font_size" data-step="1" title="Perbesar font semua baris">A+</button>
                </span>
            </div>
            @endif

            @if ($showAddress && filled($school?->address))
                <div class="alamat">{{ $school->address }}</div>
            @endif
            @if ($showContact)
                <div class="kontak">
                    Telp. {{ $school?->phone ?? '-' }}
                    @if (filled($school?->email)) &nbsp;|&nbsp; Email : {{ $school->email }} @endif
                </div>
            @endif
        </div>
    </div>
</div>