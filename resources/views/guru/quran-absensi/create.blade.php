@extends('layouts.app')

@section('title', 'Isi Absensi Al-Quran')

@section('content')
<div class="mb-6">
    <a href="{{ route('guru.quran-absensi.index') }}" class="text-sm text-slate-500 dark:text-white/40 hover:text-primary dark:hover:text-primary transition-colors">&larr; Kembali ke Daftar</a>
</div>

<div class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight">Isi Absensi Al-Quran</h1>
    <div class="mt-2 flex items-center gap-3">
        <label for="absen-date" class="text-sm text-slate-500 dark:text-white/40">Tanggal:</label>
        <input type="date" id="absen-date" value="{{ $date }}"
            class="rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-1.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 dark:focus:ring-primary/20 outline-none">
    </div>
</div>

@if ($hasSubmitted)
<div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-400">
    Absensi untuk tanggal ini sudah diisi. Data akan diperbarui jika disimpan ulang.
</div>
@endif

<form action="{{ route('guru.quran-absensi.store') }}" method="POST">
    @csrf
    <input type="hidden" name="date" value="{{ $date }}">

    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm whitespace-nowrap">
                <thead>
                    <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                        <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40 w-8">#</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Nama Siswa</th>
                        <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Kelas</th>
                        <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($students as $idx => $student)
                    <tr class="border-b border-slate-50 dark:border-white/5 last:border-0">
                        <td class="px-4 py-3 text-slate-400 dark:text-white/30">{{ $idx + 1 }}</td>
                        <td class="px-4 py-3 font-medium">{{ $student->name }}</td>
                        <td class="px-4 py-3 text-slate-500 dark:text-white/40">{{ $student->classRoom?->class_name ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                <label class="cursor-pointer">
                                    <input type="radio" name="absensi[{{ $student->id }}][status]" value="HADIR"
                                        {{ ($existingAttendance[$student->id] ?? 'HADIR') === 'HADIR' ? 'checked' : '' }}
                                        class="peer sr-only">
                                    <span class="inline-flex items-center rounded-md bg-slate-100 dark:bg-white/5 px-2.5 py-1 text-xs font-medium peer-checked:bg-primary/100 peer-checked:text-white text-slate-600 dark:text-white/60 transition-all">Hadir</span>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="absensi[{{ $student->id }}][status]" value="SAKIT"
                                        {{ ($existingAttendance[$student->id] ?? '') === 'SAKIT' ? 'checked' : '' }}
                                        class="peer sr-only">
                                    <span class="inline-flex items-center rounded-md bg-slate-100 dark:bg-white/5 px-2.5 py-1 text-xs font-medium peer-checked:bg-amber-500 peer-checked:text-white text-slate-600 dark:text-white/60 transition-all">Sakit</span>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="absensi[{{ $student->id }}][status]" value="IZIN"
                                        {{ ($existingAttendance[$student->id] ?? '') === 'IZIN' ? 'checked' : '' }}
                                        class="peer sr-only">
                                    <span class="inline-flex items-center rounded-md bg-slate-100 dark:bg-white/5 px-2.5 py-1 text-xs font-medium peer-checked:bg-blue-500 peer-checked:text-white text-slate-600 dark:text-white/60 transition-all">Izin</span>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="absensi[{{ $student->id }}][status]" value="ALPA"
                                        {{ ($existingAttendance[$student->id] ?? '') === 'ALPA' ? 'checked' : '' }}
                                        class="peer sr-only">
                                    <span class="inline-flex items-center rounded-md bg-slate-100 dark:bg-white/5 px-2.5 py-1 text-xs font-medium peer-checked:bg-red-500 peer-checked:text-white text-slate-600 dark:text-white/60 transition-all">Alpa</span>
                                </label>
                            </div>
                            <input type="hidden" name="absensi[{{ $student->id }}][student_id]" value="{{ $student->id }}">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="flex items-center gap-3">
        <button type="submit"
            class="rounded-lg bg-primary/100 hover:bg-primary text-white px-6 py-2.5 text-sm font-medium transition-colors shadow-lg shadow-primary/25">
            Simpan Absensi
        </button>
        <a href="{{ route('guru.quran-absensi.index') }}"
            class="rounded-lg border border-slate-200 dark:border-white/10 bg-white dark:bg-[#141414] px-6 py-2.5 text-sm font-medium hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
            Batal
        </a>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const dateInput = document.getElementById('absen-date');
    dateInput.addEventListener('change', () => {
        window.location.href = '{{ route("guru.quran-absensi.create") }}?date=' + dateInput.value;
    });
});
</script>
@endpush
@endsection
