@extends('layouts.app')

@section('title', 'Paket & Billing')

@php
    $tabs = [
        ['route' => 'admin.setting.profile', 'label' => 'Profil', 'active' => false],
        ['route' => 'admin.setting.password', 'label' => 'Password', 'active' => false],
        ['route' => 'admin.setting.school', 'label' => 'Sekolah', 'active' => false],
        ['route' => 'admin.setting.billing', 'label' => 'Paket & Billing', 'active' => true],
    ];

    $currency = config('plans.currency', 'Rp');
@endphp

@section('content')
@include('dashboard.subscription-notice')

<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Pengaturan</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Kelola profil dan pengaturan akun Anda.</p>
</div>

<div class="flex gap-1 mb-6 p-1 bg-slate-100 dark:bg-white/5 rounded-xl w-fit">
    @foreach ($tabs as $tab)
        <a href="{{ route($tab['route']) }}"
            class="rounded-lg px-4 py-2 text-sm font-semibold transition-all duration-150
                {{ $tab['active'] ? 'bg-white dark:bg-[#141414] text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 dark:text-white/40 hover:text-slate-900 dark:hover:text-white' }}">
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>

<div class="space-y-6">
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6 sm:p-8">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold">Paket {{ $school->planLabel() }}</h2>
                <p class="text-sm text-slate-500 dark:text-white/40 mt-1">
                    @if ($school->isTrial())
                        Masa percobaan aktif sampai {{ $school->trial_ends_at?->format('d M Y') ?? '-' }}.
                    @elseif ($school->isActive())
                        Berlangganan sejak {{ $school->subscribed_at?->format('d M Y') ?? '-' }}.
                    @endif
                </p>
            </div>
            <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase
                {{ $school->isTrial() ? 'bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400' : '' }}
                {{ $school->isActive() ? 'bg-primary/15 text-primary' : '' }}
                {{ $school->isSuspended() ? 'bg-rose-100 dark:bg-red-500/10 text-rose-700 dark:text-red-400' : '' }}
                {{ $school->isExpired() ? 'bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-white/50' : '' }}">
                {{ $school->status }}
            </span>
        </div>

        @if ($school->isTrial() || $school->isActive())
            @php
                $siswaQuota = $school->quota('siswa');
                $guruQuota = $school->quota('guru');
                $siswaLeft = $school->quotaRemaining('siswa');
                $guruLeft = $school->quotaRemaining('guru');
            @endphp
            <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6 text-sm">
                <div class="rounded-lg bg-slate-50 dark:bg-white/5 p-4">
                    <dt class="text-slate-500 dark:text-white/40 text-xs mb-1">Kuota Siswa</dt>
                    <dd class="font-semibold">
                        {{ $siswaQuota === null ? 'Tanpa batas' : $stats['siswa'].' / '.$siswaQuota }}
                        @if ($siswaQuota !== null)
                            <span class="block font-normal text-xs mt-1 {{ $siswaLeft > 0 ? 'text-slate-500 dark:text-white/40' : 'text-rose-600 dark:text-red-400' }}">
                                {{ $siswaLeft > 0 ? 'Sisa '.$siswaLeft : 'Kuota penuh' }}
                            </span>
                        @endif
                    </dd>
                </div>
                <div class="rounded-lg bg-slate-50 dark:bg-white/5 p-4">
                    <dt class="text-slate-500 dark:text-white/40 text-xs mb-1">Kuota Guru</dt>
                    <dd class="font-semibold">
                        {{ $guruQuota === null ? 'Tanpa batas' : $stats['guru'].' / '.$guruQuota }}
                        @if ($guruQuota !== null)
                            <span class="block font-normal text-xs mt-1 {{ $guruLeft > 0 ? 'text-slate-500 dark:text-white/40' : 'text-rose-600 dark:text-red-400' }}">
                                {{ $guruLeft > 0 ? 'Sisa '.$guruLeft : 'Kuota penuh' }}
                            </span>
                        @endif
                    </dd>
                </div>
                <div class="rounded-lg bg-slate-50 dark:bg-white/5 p-4">
                    <dt class="text-slate-500 dark:text-white/40 text-xs mb-1">Tagihan Berikutnya</dt>
                    <dd class="font-semibold">{{ $school->next_billing_at?->format('d M Y') ?? '-' }}</dd>
                </div>
            </dl>
        @endif

        <p class="text-sm text-slate-500 dark:text-white/40 mt-6">
            Untuk menambah kuota atau upgrade paket, hubungi pengelola platform atau Manajemen Yayasan.
        </p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach ($plans as $key => $plan)
            @php $current = $school->plan === $key; @endphp
            <div class="rounded-xl border p-6
                {{ $current ? 'bg-white dark:bg-[#141414] border-primary/60 ring-2 ring-primary/20' : 'bg-white dark:bg-[#141414] border-slate-200 dark:border-white/10' }}">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold">{{ $plan['label'] }}</h3>
                    @if ($current)
                        <span class="rounded-full bg-primary/15 px-2 py-0.5 text-xs font-semibold text-primary">Paket saat ini</span>
                    @else
                        <span class="rounded-full bg-slate-100 dark:bg-white/5 px-2 py-0.5 text-xs text-slate-500 dark:text-white/40">Bisa dinikmati setelah upgrade</span>
                    @endif
                </div>
                <p class="mt-4 text-2xl font-bold tracking-tight">
                    {{ $plan['price'] > 0 ? $currency.' '.number_format($plan['price'], 0, ',', '.') : 'Gratis' }}
                    <span class="text-sm font-normal text-slate-500 dark:text-white/40">{{ $plan['period'] }}</span>
                </p>
                <p class="mt-2 text-sm text-slate-500 dark:text-white/40">{{ $plan['tagline'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6 sm:p-8">
        <h2 class="text-lg font-semibold mb-1">Fitur Paket</h2>
        <p class="text-sm text-slate-500 dark:text-white/40 mb-6">Fitur bersifat kumulatif dari paket terendah.</p>

        <div class="overflow-x-auto">
            <table class="w-full text-sm whitespace-nowrap">
                <thead>
                    <tr class="border-b border-slate-200 dark:border-white/10 text-left">
                        <th class="py-3 pr-4 font-semibold">Fitur</th>
                        @foreach ($plans as $key => $plan)
                            <th class="py-3 px-4 font-semibold {{ $school->plan === $key ? 'text-primary' : '' }}">{{ $plan['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($features as $feature)
                        @php $fromTier = array_search($feature['from'], config('plans.order'), true); @endphp
                        <tr class="border-b border-slate-100 dark:border-white/5">
                            <td class="py-3 pr-4">
                                <span class="font-medium">{{ $feature['label'] }}</span>
                                <p class="text-xs text-slate-500 dark:text-white/40">{{ $feature['description'] }}</p>
                            </td>
                            @foreach ($plans as $key => $plan)
                                @php $colTier = array_search($key, config('plans.order'), true); @endphp
                                <td class="py-3 px-4">
                                    @if ($colTier >= $fromTier)
                                        <span class="text-primary font-bold">&#10003;</span>
                                    @else
                                        <span class="text-slate-300 dark:text-white/20 font-bold">&times;</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6 sm:p-8">
        <h2 class="text-lg font-semibold mb-4">Riwayat Pembayaran</h2>

        @forelse ($payments as $payment)
            <div class="flex flex-wrap items-center justify-between gap-3 py-3 border-b border-slate-100 dark:border-white/5">
                <div>
                    <p class="font-medium">{{ $currency.' '.number_format((float) $payment->amount, 0, ',', '.') }}</p>
                    <p class="text-xs text-slate-500 dark:text-white/40">
                        Periode {{ $payment->period_start?->format('d M Y') ?? '-' }} &ndash; {{ $payment->period_end?->format('d M Y') ?? '-' }}
                        &middot; {{ $payment->paid_at?->format('d M Y H:i') ?? '-' }}
                    </p>
                </div>
                <span class="rounded-full px-2 py-0.5 text-xs
                    {{ $payment->isActive() ? 'bg-primary/15 text-primary' : 'bg-rose-100 dark:bg-red-500/10 text-rose-700 dark:text-red-400' }}">
                    {{ $payment->isActive() ? 'Terbayar' : 'Void' }}
                </span>
            </div>
        @empty
            <p class="text-sm text-slate-500 dark:text-white/40">Belum ada pembayaran tercatat.</p>
        @endforelse
    </div>
</div>
@endsection