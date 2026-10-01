@extends('layouts.app')

@section('title', $school->name)

@section('content')
<div class="flex flex-wrap items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold tracking-tight">{{ $school->name }}</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-white/40">{{ $school->address }}</p>
    </div>
    <a href="{{ route('platform.schools.index') }}" class="rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-white/5">
        Kembali
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    <div class="lg:col-span-2 space-y-4">
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6">
            <h2 class="text-lg font-semibold mb-4">Info Sekolah</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-slate-500 dark:text-white/40">Paket</dt>
                    <dd class="font-medium">{{ $school->planLabel() }}
                        <span class="text-xs text-slate-400 dark:text-white/30">({{ $school->plan }})</span>
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500 dark:text-white/40">Status</dt>
                    <dd>
                        <span class="rounded-full px-2 py-0.5 text-xs
                            {{ $school->status === 'active' ? 'bg-primary/15 dark:bg-primary/100/10 text-primary dark:text-primary' : '' }}
                            {{ $school->status === 'trial' ? 'bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400' : '' }}
                            {{ $school->status === 'suspended' ? 'bg-rose-100 dark:bg-red-500/10 text-rose-700 dark:text-red-400' : '' }}
                            {{ $school->status === 'expired' ? 'bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-white/50' : '' }}">
                            {{ $school->status }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500 dark:text-white/40">Kontak</dt>
                    <dd>{{ $school->phone ?? '-' }}<br>{{ $school->email ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 dark:text-white/40">Trial s/d</dt>
                    <dd>{{ $school->trial_ends_at?->format('d M Y') ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 dark:text-white/40">Berlangganan sejak</dt>
                    <dd>{{ $school->subscribed_at?->format('d M Y') ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 dark:text-white/40">Tagihan berikutnya</dt>
                    <dd>{{ $school->next_billing_at?->format('d M Y') ?? '-' }}</dd>
                </div>
            </dl>
        </div>

        @if ($planLogs->isNotEmpty())
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6">
            <h2 class="text-lg font-semibold mb-4">Riwayat Perubahan Paket</h2>
            <div class="space-y-3">
                @foreach ($planLogs as $log)
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 dark:border-white/5 pb-3 text-sm">
                        <div>
                            <span class="rounded bg-slate-100 dark:bg-white/5 px-1.5 py-0.5 text-xs font-semibold">{{ $log->from_plan ?? '-' }}</span>
                            <span class="mx-1 text-slate-400">&rarr;</span>
                            <span class="rounded bg-primary/15 px-1.5 py-0.5 text-xs font-semibold text-primary">{{ $log->to_plan ?? '-' }}</span>
                            @if ($log->note)
                                <p class="mt-1 text-xs text-slate-500 dark:text-white/40">{{ $log->note }}</p>
                            @endif
                        </div>
                        <div class="text-right text-xs text-slate-500 dark:text-white/40">
                            {{ $log->created_at?->format('d M Y H:i') }}
                            @if ($log->createdBy)
                                <br>oleh {{ $log->createdBy->name }}
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6">
            <h2 class="text-lg font-semibold mb-4">Ringkasan Data</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                <div class="rounded-lg bg-slate-50 dark:bg-white/5 p-4">
                    <p class="text-2xl font-bold tracking-tight">{{ $school->users_count }}</p>
                    <p class="text-xs text-slate-500 dark:text-white/40">Pengguna</p>
                </div>
                <div class="rounded-lg bg-slate-50 dark:bg-white/5 p-4">
                    <p class="text-2xl font-bold tracking-tight">{{ $school->students_count }}</p>
                    <p class="text-xs text-slate-500 dark:text-white/40">Siswa</p>
                </div>
                <div class="rounded-lg bg-slate-50 dark:bg-white/5 p-4">
                    <p class="text-2xl font-bold tracking-tight">{{ $school->classes_count }}</p>
                    <p class="text-xs text-slate-500 dark:text-white/40">Kelas</p>
                </div>
                <div class="rounded-lg bg-slate-50 dark:bg-white/5 p-4">
                    <p class="text-2xl font-bold tracking-tight">{{ $school->subjects_count }}</p>
                    <p class="text-xs text-slate-500 dark:text-white/40">Mapel</p>
                </div>
            </div>
        </div>

        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6">
            <h2 class="text-lg font-semibold mb-4">Riwayat Pembayaran</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm whitespace-nowrap">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-white/10 text-left text-slate-500 dark:text-white/40">
                            <th class="py-2 pr-4">Tanggal</th>
                            <th class="py-2 pr-4">Nominal</th>
                            <th class="py-2 pr-4">Periode</th>
                            <th class="py-2 pr-4">Catatan</th>
                            <th class="py-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr class="border-b border-slate-100 dark:divide-white/5 {{ $payment->isVoid() ? 'opacity-50' : '' }}">
                                <td class="py-2 pr-4">
                                    @if ($payment->isVoid())
                                        <span class="line-through">{{ $payment->paid_at?->format('d M Y') }}</span>
                                        <span class="ml-1 inline-flex items-center rounded bg-red-100 dark:bg-red-500/10 px-1.5 py-0.5 text-[10px] font-bold text-red-600 dark:text-red-400">DIBATALKAN</span>
                                    @else
                                        {{ $payment->paid_at?->format('d M Y') }}
                                    @endif
                                </td>
                                <td class="py-2 pr-4 font-medium {{ $payment->isVoid() ? 'line-through' : '' }}">Rp {{ number_format($payment->amount) }}</td>
                                <td class="py-2 pr-4 text-slate-600 dark:text-white/50 {{ $payment->isVoid() ? 'line-through' : '' }}">
                                    @if ($payment->period_start && $payment->period_end)
                                        {{ $payment->period_start->format('d M') }} - {{ $payment->period_end->format('d M Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="py-2 text-slate-600 dark:text-white/50">
                                    @if ($payment->isVoid())
                                        <span class="text-xs italic">{{ $payment->void_reason }}</span>
                                    @else
                                        <span id="note-display-{{ $payment->id }}">{{ $payment->note ?? '-' }}</span>
                                        <button type="button" onclick="document.getElementById('note-display-{{ $payment->id }}').classList.add('hidden'); document.getElementById('note-edit-{{ $payment->id }}').classList.remove('hidden')"
                                            class="ml-1 text-slate-400 hover:text-primary transition-colors">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg>
                                        </button>
                                        <form id="note-edit-{{ $payment->id }}" class="hidden mt-1" method="POST" action="{{ route('platform.schools.payments.note', [$school, $payment]) }}">
                                            @csrf
                                            <div class="flex gap-1">
                                                <input type="text" name="note" value="{{ $payment->note }}" class="flex-1 rounded border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-2 py-1 text-xs">
                                                <button type="submit" class="rounded bg-primary/100 px-2 py-1 text-[10px] font-bold text-white hover:bg-primary">Simpan</button>
                                                <button type="button" onclick="document.getElementById('note-edit-{{ $payment->id }}').classList.add('hidden'); document.getElementById('note-display-{{ $payment->id }}').classList.remove('hidden')"
                                                    class="rounded bg-slate-200 dark:bg-white/10 px-2 py-1 text-[10px] font-bold text-slate-600 dark:text-white/50 hover:bg-slate-300 dark:hover:bg-white/20">Batal</button>
                                            </div>
                                        </form>
                                    @endif
                                </td>
                                <td class="py-2 text-right">
                                    @if ($payment->isActive())
                                        <button type="button" onclick="document.getElementById('modal-void-{{ $payment->id }}').classList.remove('hidden')"
                                            class="text-xs text-red-500 hover:text-red-700 dark:hover:text-red-400 font-medium transition-colors">
                                            Batalkan
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 text-center text-slate-500 dark:text-white/40">Belum ada pembayaran tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        @if ($school->isSuspended())
            <div class="rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 p-4">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                    <span class="text-sm font-semibold text-red-700 dark:text-red-400">Sekolah Ditangguhkan</span>
                </div>
                <p class="text-xs text-red-600 dark:text-red-400/80 mb-1">{{ $school->suspended_reason }}</p>
                <p class="text-[11px] text-red-500/70 dark:text-red-400/50">
                    {{ $school->suspended_at?->format('d M Y H:i') }}
                    @if ($school->suspendedBy) &middot; oleh {{ $school->suspendedBy->name }} @endif
                </p>
            </div>
            <form method="POST" action="{{ route('platform.schools.unsuspend', $school) }}">
                @csrf
                <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
                    Pulihkan Akses
                </button>
            </form>
        @else
            <button type="button" onclick="document.getElementById('modal-suspend').classList.remove('hidden')"
                class="w-full rounded-xl bg-rose-700 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-800 transition-colors">
                Tangguhkan Akses
            </button>
        @endif

        <form method="POST" action="{{ route('platform.schools.extend-trial', $school) }}">
            @csrf
            <button type="submit" class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-white/5">
                Perpanjang Trial (14 hari)
            </button>
        </form>

        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6">
            <h2 class="text-lg font-semibold mb-2">Ubah Paket</h2>
            <p class="mb-4 text-xs text-slate-500 dark:text-white/40">
                Ganti paket berlangganan sekolah secara manual. Perubahan tercatat di riwayat.
            </p>
            <form method="POST" action="{{ route('platform.schools.plan', $school) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-white/50">Paket</label>
                    <select name="plan" class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm">
                        @foreach (config('plans.plans') as $key => $plan)
                            <option value="{{ $key }}" @selected($school->plan === $key)>{{ $plan['label'] }} ({{ $key }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-white/50">Catatan</label>
                    <input type="text" name="note" class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm"
                        placeholder="Misal: Upgrade karena membeli modul RFID...">
                </div>
                <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
                    Simpan Paket
                </button>
            </form>
        </div>

        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6">
            <h2 class="text-lg font-semibold mb-2">Konfirmasi Pembayaran</h2>
            <p class="mb-4 text-xs text-slate-500 dark:text-white/40">
                Catat pembayaran masuk lalu sekolah otomatis berstatus aktif.
            </p>
            <form method="POST" action="{{ route('platform.schools.activate', $school) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-white/50">Paket</label>
                    <select name="plan" class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm">
                        <option value="">Tetap ({{ $school->planLabel() }})</option>
                        @foreach (config('plans.plans') as $key => $plan)
                            <option value="{{ $key }}">{{ $plan['label'] }} &mdash; {{ $plan['price'] > 0 ? 'Rp '.number_format($plan['price'], 0, ',', '.').$plan['period'] : 'Gratis' }}</option>
                        @endforeach
                    </select>
                    @error('plan')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-white/50">Nominal (Rp)</label>
                    <input type="number" name="amount"
                        value="{{ config('plans.plans.'.($school->plan ?? 'pro').'.price', 400000) }}"
                        class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm">
                    @error('amount')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-white/50">Dari *</label>
                        <input type="date" name="period_start" required class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm">
                        @error('period_start')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-white/50">Sampai *</label>
                        <input type="date" name="period_end" required class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm">
                        @error('period_end')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-white/50">Catatan</label>
                    <input type="text" name="note" class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm"
                        placeholder="Misal: Transfer BSI a.n. ...">
                </div>
                <button type="submit" class="w-full rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
                    Catat Pembayaran & Aktifkan
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tangguhkan -->
<div id="modal-suspend" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="document.getElementById('modal-suspend').classList.add('hidden')"></div>
    <div class="relative bg-white dark:bg-[#1a1a1a] rounded-2xl border border-slate-200 dark:border-white/10 shadow-2xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-500/10">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold">Tangguhkan Sekolah</h3>
                <p class="text-xs text-slate-500 dark:text-white/40">{{ $school->name }}</p>
            </div>
        </div>
        <p class="text-sm text-slate-600 dark:text-white/50 mb-4">
            Semua pengguna di sekolah ini akan keluar otomatis dan tidak dapat mengakses sistem sampai akses dipulihkan.
        </p>
        <form method="POST" action="{{ route('platform.schools.suspend', $school) }}" class="space-y-4">
            @csrf
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-white/50">Alasan penangguhan *</label>
                <textarea name="suspended_reason" rows="3" required
                    class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500/20"
                    placeholder="Contoh: Pelanggaran ketentuan penggunaan sistem..."></textarea>
                @error('suspended_reason')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="document.getElementById('modal-suspend').classList.add('hidden')"
                    class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-white/50 hover:text-slate-800 dark:hover:text-white/70 transition-colors">
                    Batal
                </button>
                <button type="submit"
                    class="px-4 py-2 text-sm font-semibold text-white bg-rose-700 rounded-lg hover:bg-rose-800 transition-colors">
                    Tangguhkan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Void Payment Modals -->
@foreach ($payments->filter(fn ($p) => $p->isActive()) as $payment)
<div id="modal-void-{{ $payment->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="document.getElementById('modal-void-{{ $payment->id }}').classList.add('hidden')"></div>
    <div class="relative bg-white dark:bg-[#1a1a1a] rounded-2xl border border-slate-200 dark:border-white/10 shadow-2xl w-full max-w-md mx-4 p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-500/10">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
            </div>
            <div>
                <h3 class="text-lg font-semibold">Batalkan Pembayaran</h3>
                <p class="text-xs text-slate-500 dark:text-white/40">Rp {{ number_format($payment->amount) }} · {{ $payment->paid_at?->format('d M Y') }}</p>
            </div>
        </div>
        <p class="text-sm text-slate-600 dark:text-white/50 mb-4">
            Pembayaran ini akan ditandai sebagai dibatalkan. Catatan asli tetap tersimpan untuk audit.
        </p>
        <form method="POST" action="{{ route('platform.schools.payments.void', [$school, $payment]) }}" class="space-y-4">
            @csrf
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600 dark:text-white/50">Alasan pembatalan *</label>
                <textarea name="void_reason" rows="3" required
                    class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-amber-500 focus:ring-amber-500/20"
                    placeholder="Contoh: Tanggal input salah..."></textarea>
                @error('void_reason')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="document.getElementById('modal-void-{{ $payment->id }}').classList.add('hidden')"
                    class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-white/50 hover:text-slate-800 dark:hover:text-white/70 transition-colors">
                    Batal
                </button>
                <button type="submit"
                    class="px-4 py-2 text-sm font-semibold text-white bg-amber-600 rounded-lg hover:bg-amber-700 transition-colors">
                    Batalkan
                </button>
            </div>
        </form>
    </div>
</div>
@endforeach
@endsection
