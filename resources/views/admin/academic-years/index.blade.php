@extends('layouts.app')

@section('title', 'Tahun Ajaran')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight">Tahun Ajaran</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Kelola tahun ajaran, promosi siswa, dan data historis.</p>
</div>

@if (session('success'))
<div class="mb-4 rounded-lg bg-primary/10 dark:bg-primary/100/10 border border-primary/20 dark:border-primary/20 p-4 text-sm text-primary dark:text-primary">{{ session('success') }}</div>
@endif
@if (session('error'))
<div class="mb-4 rounded-lg bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 p-4 text-sm text-red-700 dark:text-red-400">{{ session('error') }}</div>
@endif

<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden mb-6">
    <div class="p-4 border-b border-slate-100 dark:border-white/5 flex items-center justify-between">
        <h2 class="font-semibold">Daftar Tahun Ajaran</h2>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Nama</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40 w-32">Status</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40 w-24">Data</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40 w-48">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($academicYears as $ay)
                <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 {{ $ay->is_active ? 'bg-primary/10/50 dark:bg-primary/100/5' : 'hover:bg-slate-50 dark:hover:bg-white/[0.02]' }} transition-colors">
                    <td class="px-4 py-2.5">
                        <div class="font-medium">{{ $ay->name }}</div>
                        <div class="text-xs text-slate-400 dark:text-white/30">{{ \Carbon\Carbon::parse($ay->start_date)->format('d M Y') }} — {{ \Carbon\Carbon::parse($ay->end_date)->format('d M Y') }}</div>
                    </td>
                    <td class="px-4 py-2.5 text-center">
                        @if ($ay->is_active)
                        <span class="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/100/10 px-2 py-0.5 text-xs font-medium text-primary dark:text-primary">Aktif</span>
                        @else
                        <span class="inline-flex items-center rounded-md bg-slate-100 dark:bg-white/5 px-2 py-0.5 text-xs font-medium text-slate-500 dark:text-white/30">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-4 py-2.5 text-center text-xs text-slate-500 dark:text-white/40">
                        {{ $ay->grades_count }} nilai
                    </td>
                    <td class="px-4 py-2.5">
                        <div class="flex items-center justify-center gap-2 flex-wrap">
                            @if (!$ay->is_active)
                            <form method="POST" action="{{ route('admin.academic-years.activate', $ay) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="rounded-lg bg-primary/100 hover:bg-primary text-white px-3 py-1.5 text-xs font-medium transition-colors">
                                    Aktifkan
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.academic-years.promote', $ay) }}" onsubmit="return confirm('Promosi semua siswa naik kelas? Siswa kelas IX akan lulus.')">
                                @csrf @method('PATCH')
                                <button type="button" class="rounded-lg border border-amber-300 dark:border-amber-500/30 text-amber-700 dark:text-amber-400 px-3 py-1.5 text-xs font-medium hover:bg-amber-50 dark:hover:bg-amber-500/10 transition-colors">
                                    Promosi
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.academic-years.destroy', $ay) }}" onsubmit="return confirm('Hapus tahun ajaran ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="rounded-lg border border-red-200 dark:border-red-500/20 text-red-600 dark:text-red-400 px-3 py-1.5 text-xs font-medium hover:bg-red-50 dark:hover:bg-red-500/10 transition-colors">
                                    Hapus
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-slate-400 dark:text-white/30 text-sm">Belum ada tahun ajaran.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
    <h3 class="font-semibold mb-4">Tambah Tahun Ajaran</h3>
    <form method="POST" action="{{ route('admin.academic-years.store') }}">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Nama</label>
                <input type="text" name="name" required maxlength="20"
                    class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80"
                    placeholder="2027/2028">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Tanggal Mulai</label>
                <input type="date" name="start_date" required
                    class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Tanggal Selesai</label>
                <input type="date" name="end_date" required
                    class="w-full rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm focus:border-primary focus:ring-primary/20 dark:text-white/80">
            </div>
            <div>
                <button type="submit" class="w-full rounded-lg bg-primary/100 hover:bg-primary text-white px-4 py-2 text-sm font-medium transition-colors">
                    Tambah
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
