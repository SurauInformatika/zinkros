@extends('layouts.app')

@section('title', 'Desain Rapor')

@section('content')
<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Desain Format Rapor</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-white/40">
            Susun format rapor dengan blok — judul, tabel, baris, dan ttd. Format aktif otomatis dipakai saat wali kelas mencetak rapor.
        </p>
    </div>
    <a href="{{ route('wakasek.rapor-design.create') }}"
        class="shrink-0 inline-flex items-center gap-1.5 rounded-lg bg-primary/100 hover:bg-primary text-white px-4 py-2 text-sm font-medium transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        Desain Baru
    </a>
</div>

@if ($templates->isEmpty())
<div class="rounded-xl border border-slate-200 bg-white dark:border-white/10 dark:bg-[#141414] p-12 text-center">
    <p class="text-sm text-slate-400 dark:text-white/30">
        Belum ada desain rapor. Klik "Desain Baru" untuk membuat format — selama belum ada format, wali kelas mencetak rapor standar bawaan.
    </p>
</div>
@else
<div class="grid gap-4">
    @foreach ($templates as $template)
    <div class="rounded-xl border {{ $template->is_active ? 'border-primary/40 dark:border-primary/40' : 'border-slate-200 dark:border-white/10' }} bg-white dark:bg-[#141414] p-5 flex flex-col sm:flex-row sm:items-center gap-4">
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <h3 class="font-semibold truncate">{{ $template->name }}</h3>
                @if ($template->is_active)
                <span class="inline-flex items-center rounded-full bg-primary/10 text-primary px-2.5 py-0.5 text-[11px] font-semibold">Aktif</span>
                @endif
            </div>
            <p class="mt-1 text-xs text-slate-500 dark:text-white/40">
                Tahun ajaran: {{ $template->academicYear?->name ?? 'Semua tahun' }}
                &middot; Diperbarui: {{ $template->updated_at->format('d M Y H:i') }}
                @if ($template->creator)
                &middot; Oleh: {{ $template->creator->name }}
                @endif
            </p>
            <p class="mt-1 text-xs text-slate-400 dark:text-white/25">
                {{ count($template->blocks ?? []) }} blok
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap shrink-0">
            @if (! $template->is_active)
            <form method="POST" action="{{ route('wakasek.rapor-design.activate', $template->id) }}">
                @csrf
                <button type="submit"
                    class="rounded-lg bg-primary/100 hover:bg-primary text-white px-3 py-1.5 text-xs font-medium transition-colors">Aktifkan</button>
            </form>
            @endif
            <a href="{{ route('wakasek.rapor-design.edit', $template->id) }}"
                class="rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-transparent text-slate-700 dark:text-white px-3 py-1.5 text-xs font-medium transition-colors hover:bg-slate-50 dark:hover:bg-white/5">Edit</a>
            <form method="POST" action="{{ route('wakasek.rapor-design.duplicate', $template->id) }}">
                @csrf
                <button type="submit"
                    class="rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-transparent text-slate-700 dark:text-white px-3 py-1.5 text-xs font-medium transition-colors hover:bg-slate-50 dark:hover:bg-white/5">Duplikat</button>
            </form>
            <form method="POST" action="{{ route('wakasek.rapor-design.destroy', $template->id) }}"
                onsubmit="return confirm('Hapus desain rapor ini? @if ($template->is_active)Format aktif akan digantikan desain lain. @endif')">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="rounded-lg border border-red-200 dark:border-red-500/30 text-red-600 dark:text-red-400 px-3 py-1.5 text-xs font-medium transition-colors hover:bg-red-50 dark:hover:bg-red-500/10">Hapus</button>
            </form>
        </div>
    </div>
    @endforeach
</div>
@endif

<p class="mt-6 text-xs text-slate-400 dark:text-white/30">
    Perlu bantuan? Mulai dari "Desain Baru" — format bawaan berisi kop, judul, identitas, tabel nilai, tahfidz, ekstrakurikuler, ketidakhadiran, catatan wali kelas, dan tanda tangan persis seperti rapor standar pemerintah.
</p>
@endsection