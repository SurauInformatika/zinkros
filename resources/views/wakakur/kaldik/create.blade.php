@extends('layouts.app')

@section('title', 'Buat Kalender Pendidikan')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Buat Kalender Pendidikan</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Buat KALDIK baru dari template atau kosong.</p>
</div>

@if ($errors->any())
<div class="mb-4 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 p-4 text-sm text-red-700 dark:text-red-400">
    <ul class="list-disc list-inside">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="max-w-2xl rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6 sm:p-8">
    <form method="POST" action="{{ route('wakasek.kaldik.store') }}" class="space-y-5">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Nama Kalender</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required
                class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('name') border-red-400 @enderror"
                placeholder="contoh: KALDIK 2026/2027">
            @error('name')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="academic_year_id" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Tahun Ajaran</label>
            <select id="academic_year_id" name="academic_year_id" required
                class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('academic_year_id') border-red-400 @enderror">
                <option value="">Pilih Tahun Ajaran</option>
                @foreach (\App\Models\AcademicYear::where('school_id', auth()->user()->school_id)->get() as $year)
                    <option value="{{ $year->id }}" {{ old('academic_year_id', $activeYear?->id) == $year->id ? 'selected' : '' }}>{{ $year->name }} {{ $year->is_active ? '(Aktif)' : '' }}</option>
                @endforeach
            </select>
            @error('academic_year_id')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-slate-400 dark:text-white/30">KALDIK dibuat untuk <strong>satu tahun ajaran penuh</strong> (semua bulan pada TA terpilih).</p>
        </div>

        <div>
            <label for="template_id" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Template (opsional)</label>
            <select id="template_id" name="template_id"
                class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('template_id') border-red-400 @enderror">
                <option value="">Tanpa Template (Kosong)</option>
                @foreach ($templates as $tpl)
                    <option value="{{ $tpl->id }}" {{ old('template_id') == $tpl->id ? 'selected' : '' }}>{{ $tpl->name }} ({{ $tpl->source }})</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-400 dark:text-white/30">Pilih template untuk auto-fill struktur JP.</p>
            @error('template_id')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="source" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Sumber</label>
            <select id="source" name="source" required
                class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('source') border-red-400 @enderror">
                <option value="custom" {{ old('source') === 'custom' ? 'selected' : '' }}>Custom</option>
                <option value="kemenag" {{ old('source') === 'kemenag' ? 'selected' : '' }}>Kemenag</option>
                <option value="dindik" {{ old('source') === 'dindik' ? 'selected' : '' }}>Dinas Pendidikan</option>
            </select>
            @error('source')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4" id="rangeFields">
            <div>
                <label for="start_date" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Mulai Semester 1</label>
                <input id="start_date" name="start_date" type="date" value="{{ old('start_date') }}" required
                    class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('start_date') border-red-400 @enderror">
                @error('start_date')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="semester_2_start_date" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Mulai Semester 2 <span class="text-xs text-slate-400">(khusus · opsional)</span></label>
                <input id="semester_2_start_date" name="semester_2_start_date" type="date" value="{{ old('semester_2_start_date') }}"
                    class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('semester_2_start_date') border-red-400 @enderror">
                @error('semester_2_start_date')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="end_date" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Selesai Semester 2</label>
                <input id="end_date" name="end_date" type="date" value="{{ old('end_date') }}" required
                    class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('end_date') border-red-400 @enderror">
                @error('end_date')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div id="rangePreview" class="mt-4 rounded-xl border border-primary/20 dark:border-primary/20 bg-primary/10/60 dark:bg-primary/100/5 px-4 py-3 text-sm text-slate-600 dark:text-white/60">
            <span class="font-medium text-primary dark:text-primary">Semester 1:</span> <span id="previewS1">—</span>
            <span class="mx-2 text-slate-300 dark:text-white/20">|</span>
            <span class="font-medium text-primary dark:text-primary">Semester 2:</span> <span id="previewS2">—</span>
            <span id="previewNote" class="mt-1 block text-xs text-slate-400 dark:text-white/30"></span>
        </div>

        <script>
            const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            function fmt(d) {
                if (!d) return '—';
                const x = new Date(d + 'T00:00:00');
                return x.getDate() + ' ' + MONTHS[x.getMonth()] + ' ' + x.getFullYear();
            }
            function updateRange() {
                const s1 = document.getElementById('start_date')?.value;
                const s2 = document.getElementById('semester_2_start_date')?.value;
                const end = document.getElementById('end_date')?.value;
                const pS1 = document.getElementById('previewS1');
                const pS2 = document.getElementById('previewS2');
                const pN = document.getElementById('previewNote');
                if (!s1 || !end) { pS1.textContent = '—'; pS2.textContent = '—'; pN.textContent = ''; return; }
                if (s2) {
                    const day = new Date(s2 + 'T00:00:00');
                    day.setDate(day.getDate() - 1);
                    const s1End = day.getFullYear() + '-' + String(day.getMonth() + 1).padStart(2, '0') + '-' + String(day.getDate()).padStart(2, '0');
                    pS1.textContent = fmt(s1) + ' – ' + fmt(s1End);
                    pS2.textContent = fmt(s2) + ' – ' + fmt(end);
                    pN.textContent = 'Pemisahan blok Ganjil/Genap di dokumen memakai tanggal mulai Semester 2 ini.';
                } else {
                    pS1.textContent = fmt(s1) + ' – ' + fmt(end);
                    pS2.textContent = 'otomatis';
                    pN.textContent = 'Kosong = blok Ganjil/Genap dibagi per bulan (Jul–Des & Jan–Jun). Isi tanggal khusus bila mulai Semester 2 bukan awal tahun.';
                }
            }
            ['start_date', 'semester_2_start_date', 'end_date'].forEach(function (id) {
                const el = document.getElementById(id);
                if (el) { el.addEventListener('change', updateRange); el.addEventListener('input', updateRange); }
            });
            updateRange();
        </script>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
                Buat Kalender
            </button>
            <a href="{{ route('wakasek.kaldik.index') }}" class="text-sm text-slate-500 hover:text-slate-700 dark:text-white/40 dark:hover:text-white/60">Batal</a>
        </div>
    </form>
</div>
@endsection
