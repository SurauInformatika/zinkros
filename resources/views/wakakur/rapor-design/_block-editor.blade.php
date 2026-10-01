@php
    $ic = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0a0a0a] dark:text-white placeholder:text-slate-400';
    $lb = 'block text-xs font-semibold text-slate-500 dark:text-white/40 mb-1';
    $chk = 'h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary';
    $props = $block['props'] ?? [];
    $bid = $block['id'] ?? ('b'.$i);
    $typeLabel = \App\Support\RaporFormat::labels()[$block['type']] ?? $block['type'];
@endphp
<div class="rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#141414] overflow-hidden">
    <div class="flex items-center justify-between px-4 py-2 bg-slate-50 dark:bg-white/5 border-b border-slate-100 dark:border-white/5">
        <span class="text-xs font-bold uppercase tracking-wide text-slate-500 dark:text-white/50">
            Pengaturan {{ $typeLabel }}
        </span>
        <button type="button" data-act="properties" data-target="{{ $bid }}" title="Tutup panel"
            class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    <div class="p-4 space-y-4">
        @switch($block['type'])
        @case('kop')
            <div class="grid grid-cols-3 gap-3">
                @foreach ([
                    ['p' => 'show_logo', 'l' => 'Tampilkan Logo'],
                    ['p' => 'show_address', 'l' => 'Tampilkan Alamat'],
                    ['p' => 'show_contact', 'l' => 'Tampilkan Kontak'],
                ] as $t)
                <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-white/70">
                    <input type="checkbox" data-b="{{ $i }}" data-path="props.{{ $t['p'] }}"
                        class="{{ $chk }}" {{ ($props[$t['p']] ?? true) ? 'checked' : '' }}>
                    {{ $t['l'] }}
                </label>
                @endforeach
            </div>
            <div class="grid grid-cols-3 gap-3 items-end">
                <div class="col-span-1">
                    <label class="{{ $lb }}">Ukuran Font Judul (px)</label>
                    <input type="number" min="8" max="40" data-b="{{ $i }}" data-path="props.title_font_size"
                        value="{{ $props['title_font_size'] ?? 15 }}" class="{{ $ic }}">
                </div>
                <p class="col-span-2 text-xs text-slate-400 dark:text-white/30 leading-relaxed">
                    Judul kop, tambah/kurang baris, ukuran font, dan logo (drag &amp; drop) bisa diedit langsung di preview.
                </p>
            </div>
            @break

        @case('judul')
            <p class="col-span-2 text-xs text-slate-400 dark:text-white/30 leading-relaxed">
                Teks judul dan ukuran font bisa diedit langsung di preview (klik teksnya).
            </p>
            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-white/70">
                <input type="checkbox" data-b="{{ $i }}" data-path="props.show_semester"
                    class="{{ $chk }}" {{ ($props['show_semester'] ?? true) ? 'checked' : '' }}>
                Tampilkan semester &amp; tahun pelajaran
            </label>
            @break

        @case('section')
            <div>
                <label class="{{ $lb }}">Teks Bagian</label>
                <input type="text" data-b="{{ $i }}" data-path="props.text" value="{{ $props['text'] ?? '' }}" class="{{ $ic }}">
            </div>
            @break

        @case('identitas')
            @php
                $idSrc = [
                    'name' => 'Nama', 'nisn_nis' => 'NISN / NIS', 'gender' => 'Jenis Kelamin',
                    'school_name' => 'Nama Sekolah', 'class_semester' => 'Kelas / Semester',
                    'tahun' => 'Tahun Pelajaran', 'none' => 'Kosong (isi manual)',
                ];
                $idCols = ['left' => 'Kiri', 'right' => 'Kanan'];
            @endphp
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                @foreach (['left', 'right'] as $col)
                <div>
                    <span class="{{ $lb }}">{{ $col === 'left' ? 'Kolom Kiri' : 'Kolom Kanan' }}</span>
                    <div class="space-y-2">
                        @foreach (($props[$col] ?? []) as $ir => $row)
                        <div class="flex items-center gap-2">
                            <input type="text" data-b="{{ $i }}" data-path="props.{{ $col }}.{{ $ir }}.label"
                                value="{{ $row['label'] ?? '' }}" placeholder="Label (mis. Nama Lengkap)" class="{{ $ic }}">
                            <select data-b="{{ $i }}" data-path="props.{{ $col }}.{{ $ir }}.key" class="{{ $ic }} !w-auto">
                                @foreach ($idSrc as $k => $lbl)
                                <option value="{{ $k }}" @selected(($row['key'] ?? 'none') === $k)>{{ $lbl }}</option>
                                @endforeach
                            </select>
                            <button type="button" data-act="list-move" data-target="{{ $bid }}" data-path="{{ $col }}" data-idx="{{ $ir }}" data-dir="up"
                                title="Naikkan baris"
                                class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-50 dark:hover:bg-white/5 transition-colors shrink-0">↑</button>
                            <button type="button" data-act="list-move" data-target="{{ $bid }}" data-path="{{ $col }}" data-idx="{{ $ir }}" data-dir="down"
                                title="Turunkan baris"
                                class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-50 dark:hover:bg-white/5 transition-colors shrink-0">↓</button>
                            <button type="button" data-act="col-move" data-target="{{ $bid }}" data-path="{{ $col }}" data-idx="{{ $ir }}"
                                title="Pindah ke kolom {{ $idCols[$col === 'left' ? 'right' : 'left'] }}"
                                class="p-2 rounded-lg text-slate-400 hover:text-primary hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition-colors shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4"/></svg>
                            </button>
                            <button type="button" data-act="list-del" data-target="{{ $bid }}" data-path="{{ $col }}" data-idx="{{ $ir }}"
                                class="p-2 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        @endforeach
                    </div>
                    <button type="button" data-act="list-add" data-target="{{ $bid }}" data-path="{{ $col }}"
                        class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-primary hover:text-primary transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        Tambah Baris ({{ $idCols[$col] }})
                    </button>
                </div>
                @endforeach
            </div>
            @break

        @case('tabel-nilai')
            <div>
                <label class="{{ $lb }}">Judul Bagian</label>
                <input type="text" data-b="{{ $i }}" data-path="props.title" value="{{ $props['title'] ?? '' }}" class="{{ $ic }}">
            </div>
            <div class="grid grid-cols-2 gap-x-4 gap-y-2 sm:grid-cols-3">
                @foreach ([
                    ['p' => 'show_no', 'l' => 'Kolom No'],
                    ['p' => 'show_kkm', 'l' => 'Kolom KKM'],
                    ['p' => 'show_final', 'l' => 'Kolom Nilai Akhir'],
                    ['p' => 'show_predikat', 'l' => 'Kolom Predikat'],
                    ['p' => 'show_keterangan', 'l' => 'Catatan kaki'],
                ] as $t)
                <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-white/70">
                    <input type="checkbox" data-b="{{ $i }}" data-path="props.{{ $t['p'] }}"
                        class="{{ $chk }}" {{ ($props[$t['p']] ?? true) ? 'checked' : '' }}>
                    {{ $t['l'] }}
                </label>
                @endforeach
            </div>
            <div>
                <span class="{{ $lb }}">Kolom per Tipe Penilaian</span>
                <div class="grid grid-cols-2 gap-x-4 gap-y-2 sm:grid-cols-3">
                    @php $selTypes = $props['grade_type_ids'] ?? []; @endphp
                    @forelse ($gradeTypes as $gt)
                    <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-white/70">
                        <input type="checkbox" data-b="{{ $i }}" data-path="props.grade_type_ids" value="{{ $gt->id }}"
                            class="{{ $chk }}" @checked(in_array($gt->id, $selTypes, true))>
                        {{ $gt->name }} ({{ $gt->weight }}%)
                    </label>
                    @empty
                    <p class="text-xs text-slate-400 dark:text-white/30">Belum ada tipe penilaian (admin tipe nilai). Semua kolom tipe ditampilkan.</p>
                    @endforelse
                </div>
                <p class="mt-1 text-[11px] text-slate-400 dark:text-white/30">Kosongkan pilihan = tampilkan semua tipe nilai aktif.</p>
            </div>
            <div>
                <span class="{{ $lb }}">Kolom Bebas (sel kosong, diisi manual)</span>
                <div class="space-y-2">
                    @foreach (($props['custom_columns'] ?? []) as $ci => $col)
                    <div class="flex items-center gap-2">
                        <input type="text" data-b="{{ $i }}" data-path="props.custom_columns.{{ $ci }}.label"
                            value="{{ $col['label'] ?? '' }}" placeholder="Nama kolom" class="{{ $ic }}">
                        <button type="button" data-act="list-del" data-target="{{ $bid }}" data-path="custom_columns" data-idx="{{ $ci }}"
                            class="p-2 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    @endforeach
                </div>
                <button type="button" data-act="list-add" data-target="{{ $bid }}" data-path="custom_columns"
                    class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-primary hover:text-primary transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Tambah Kolom
                </button>
            </div>
            @break

        @case('tabel-tahfidz')
            <div>
                <label class="{{ $lb }}">Judul Bagian</label>
                <input type="text" data-b="{{ $i }}" data-path="props.title" value="{{ $props['title'] ?? '' }}" class="{{ $ic }}">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600 dark:text-white/70">
                <input type="checkbox" data-b="{{ $i }}" data-path="props.show_surah"
                    class="{{ $chk }}" {{ ($props['show_surah'] ?? true) ? 'checked' : '' }}>
                Tampilkan daftar surah yang dihafalkan
            </label>
            @break

        @case('tabel-bebas')
            <div>
                <label class="{{ $lb }}">Judul Bagian</label>
                <input type="text" data-b="{{ $i }}" data-path="props.title" value="{{ $props['title'] ?? '' }}" class="{{ $ic }}">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="{{ $lb }}">Jumlah Baris Kosong</label>
                    <input type="number" min="0" max="20" data-b="{{ $i }}" data-path="props.rows"
                        value="{{ $props['rows'] ?? 0 }}" class="{{ $ic }}">
                </div>
            </div>
            <div>
                <span class="{{ $lb }}">Kolom</span>
                <div class="space-y-2">
                    @foreach (($props['columns'] ?? []) as $ci => $col)
                    <div class="flex items-center gap-2">
                        <input type="text" data-b="{{ $i }}" data-path="props.columns.{{ $ci }}.label"
                            value="{{ $col['label'] ?? '' }}" placeholder="Nama kolom" class="{{ $ic }}">
                        <button type="button" data-act="list-del" data-target="{{ $bid }}" data-path="columns" data-idx="{{ $ci }}"
                            class="p-2 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    @endforeach
                </div>
                <button type="button" data-act="list-add" data-target="{{ $bid }}" data-path="columns"
                    class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-primary hover:text-primary transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Tambah Kolom
                </button>
            </div>
            @break

        @case('tabel-absensi')
            <div>
                <label class="{{ $lb }}">Judul Bagian</label>
                <input type="text" data-b="{{ $i }}" data-path="props.title" value="{{ $props['title'] ?? '' }}" class="{{ $ic }}">
            </div>
            @break

        @case('catatan')
            <div>
                <label class="{{ $lb }}">Judul Bagian</label>
                <input type="text" data-b="{{ $i }}" data-path="props.title" value="{{ $props['title'] ?? '' }}" class="{{ $ic }}">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="{{ $lb }}">Jumlah Baris</label>
                    <input type="number" min="1" max="12" data-b="{{ $i }}" data-path="props.lines"
                        value="{{ $props['lines'] ?? 3 }}" class="{{ $ic }}">
                </div>
            </div>
            @break

        @case('ttd')
            <div>
                <span class="{{ $lb }}">Kotak Tanda Tangan</span>
                <div class="space-y-2">
                    @foreach (($props['boxes'] ?? []) as $bi => $box)
                    <div class="flex items-center gap-2">
                        <input type="text" data-b="{{ $i }}" data-path="props.boxes.{{ $bi }}.label"
                            value="{{ $box['label'] ?? '' }}" placeholder="Label (mis. Orang Tua / Wali)" class="{{ $ic }}">
                        <select data-b="{{ $i }}" data-path="props.boxes.{{ $bi }}.key" class="{{ $ic }} !w-auto">
                            @foreach ([
                                'ortu' => 'Nama Orang Tua (otomatis)', 'wali' => 'Nama Wali Kelas (otomatis)',
                                'kepsek' => 'Nama Kepala Sekolah (otomatis)', 'manual' => 'Kosong (isi manual)',
                            ] as $k => $lbl)
                            <option value="{{ $k }}" @selected(($box['key'] ?? 'manual') === $k)>{{ $lbl }}</option>
                            @endforeach
                        </select>
                        <button type="button" data-act="list-del" data-target="{{ $bid }}" data-path="boxes" data-idx="{{ $bi }}"
                            class="p-2 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    @endforeach
                </div>
                <button type="button" data-act="list-add" data-target="{{ $bid }}" data-path="boxes"
                    class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-primary hover:text-primary transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Tambah Kotak
                </button>
            </div>
            @break

        @case('teks-bebas')
            <div>
                <label class="{{ $lb }}">Teks</label>
                <textarea rows="2" data-b="{{ $i }}" data-path="props.text" class="{{ $ic }}">{{ $props['text'] ?? '' }}</textarea>
            </div>
            @break
        @endswitch
    </div>
</div>