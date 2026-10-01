@extends('layouts.app')

@section('title', 'Libur Rutin')

@php
    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
@endphp

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Libur Rutin</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Kelola libur nasional dan rutin yang berlaku untuk semua sekolah.</p>
    </div>
    <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="rounded-lg bg-primary/100 hover:bg-primary text-white px-4 py-2 text-sm font-medium transition-colors">
        + Tambah Libur
    </button>
</div>

@if (session('status'))
<div class="mb-4 rounded-lg bg-primary/10 dark:bg-primary/100/10 border border-primary/20 dark:border-primary/20 p-4 text-sm text-primary dark:text-primary">{{ session('status') }}</div>
@endif

<div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-100 dark:border-white/5 bg-slate-50 dark:bg-white/[0.02]">
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Nama</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Tanggal</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Tipe</th>
                    <th class="px-4 py-3 text-center font-medium text-slate-500 dark:text-white/40">Status</th>
                    <th class="px-4 py-3 text-left font-medium text-slate-500 dark:text-white/40">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($holidays as $holiday)
                <tr class="border-b border-slate-50 dark:border-white/5 last:border-0 hover:bg-slate-50 dark:hover:bg-white/[0.02]">
                    <td class="px-4 py-3 font-medium">{{ $holiday->name }}</td>
                    <td class="px-4 py-3 text-slate-500 dark:text-white/40">
                        {{ $holiday->day ? $holiday->day . ' ' : '' }}{{ $months[$holiday->month] ?? '' }}
                        @if ($holiday->calendar_type === 'hijriah')
                            <span class="text-[10px] text-amber-500 dark:text-amber-400 ml-1">(Hijriah)</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center rounded-md bg-slate-100 dark:bg-white/10 px-2 py-0.5 text-xs font-medium text-slate-700 dark:text-white/60 capitalize">{{ $holiday->calendar_type }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if ($holiday->is_active)
                            <span class="inline-flex items-center rounded-md bg-primary/10 dark:bg-primary/100/10 px-2 py-0.5 text-xs font-medium text-primary dark:text-primary">Aktif</span>
                        @else
                            <span class="inline-flex items-center rounded-md bg-slate-100 dark:bg-white/10 px-2 py-0.5 text-xs font-medium text-slate-500 dark:text-white/40">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <button onclick="editHoliday({{ json_encode($holiday) }})" class="text-primary dark:text-primary hover:underline text-xs">Edit</button>
                            <form method="POST" action="{{ route('platform.recurring-holidays.destroy', $holiday) }}" onsubmit="return confirm('Hapus libur ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 dark:text-red-400 hover:underline text-xs">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-slate-400 dark:text-white/30">Belum ada libur rutin.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white dark:bg-[#141414] rounded-xl border border-slate-200 dark:border-white/10 p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-semibold mb-4">Tambah Libur Rutin</h3>
        <form method="POST" action="{{ route('platform.recurring-holidays.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Nama Libur</label>
                <input type="text" name="name" required
                    class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"
                    placeholder="contoh: HUT RI">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Bulan</label>
                    <select name="month" required class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}">{{ $months[$m] }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Tanggal</label>
                    <input type="number" name="day" min="1" max="31"
                        class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition"
                        placeholder="Kosongkan jika Hijriah">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Tipe Kalender</label>
                <select name="calendar_type" required class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                    <option value="masehi">Masehi</option>
                    <option value="hijriah">Hijriah</option>
                </select>
            </div>
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="text-sm text-slate-500 hover:text-slate-700 dark:text-white/40 dark:hover:text-white/60">Batal</button>
                <button type="submit" class="rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white dark:bg-[#141414] rounded-xl border border-slate-200 dark:border-white/10 p-6 w-full max-w-md mx-4">
        <h3 class="text-lg font-semibold mb-4">Edit Libur Rutin</h3>
        <form id="editForm" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Nama Libur</label>
                <input type="text" name="name" id="edit_name" required
                    class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Bulan</label>
                    <select name="month" id="edit_month" required class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}">{{ $months[$m] }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Tanggal</label>
                    <input type="number" name="day" id="edit_day" min="1" max="31"
                        class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Tipe Kalender</label>
                <select name="calendar_type" id="edit_calendar_type" required class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition">
                    <option value="masehi">Masehi</option>
                    <option value="hijriah">Hijriah</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" id="edit_is_active"
                    class="rounded border-slate-300 dark:border-white/10 text-primary focus:ring-primary/20">
                <label class="text-sm text-slate-700 dark:text-white/70">Aktif</label>
            </div>
            <div class="flex items-center justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" class="text-sm text-slate-500 hover:text-slate-700 dark:text-white/40 dark:hover:text-white/60">Batal</button>
                <button type="submit" class="rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editHoliday(holiday) {
    var form = document.getElementById('editForm');
    form.action = '{{ url("platform/recurring-holidays") }}/' + holiday.id;
    document.getElementById('edit_name').value = holiday.name;
    document.getElementById('edit_month').value = holiday.month;
    document.getElementById('edit_day').value = holiday.day || '';
    document.getElementById('edit_calendar_type').value = holiday.calendar_type;
    document.getElementById('edit_is_active').checked = holiday.is_active;
    document.getElementById('editModal').classList.remove('hidden');
}
</script>
@endpush
