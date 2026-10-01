@extends('layouts.app')

@section('title', 'Dashboard Platform')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Dashboard Platform</h1>
    <form method="POST" action="{{ route('auth.logout') }}">
        @csrf
        <button type="submit" class="rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] px-4 py-2 text-sm hover:bg-slate-50 dark:hover:bg-white/5">
            Keluar
        </button>
    </form>
</div>

<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
        <p class="text-xs text-slate-500 dark:text-white/40">Total Sekolah</p>
        <p class="mt-1 text-2xl font-bold tracking-tight">{{ number_format($stats['total']) }}</p>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
        <p class="text-xs text-slate-500 dark:text-white/40">Aktif</p>
        <p class="mt-1 text-2xl font-bold tracking-tight text-primary">{{ number_format($stats['active']) }}</p>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
        <p class="text-xs text-slate-500 dark:text-white/40">Trial</p>
        <p class="mt-1 text-2xl font-bold tracking-tight text-amber-600">{{ number_format($stats['trial']) }}</p>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
        <p class="text-xs text-slate-500 dark:text-white/40">Ditangguhkan</p>
        <p class="mt-1 text-2xl font-bold tracking-tight text-rose-600">{{ number_format($stats['suspended']) }}</p>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
        <p class="text-xs text-slate-500 dark:text-white/40">Pendapatan Tercatat</p>
        <p class="mt-1 text-2xl font-bold tracking-tight">Rp {{ number_format($stats['revenue'], 0, ',', '.') }}</p>
    </div>
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
        <p class="text-xs text-slate-500 dark:text-white/40">Potensi / Bulan</p>
        <p class="mt-1 text-2xl font-bold tracking-tight text-primary">Rp {{ number_format($stats['monthly_potential'], 0, ',', '.') }}</p>
    </div>
</div>

<div class="mt-6 grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2 rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold">Distribusi Paket</h2>
            <span class="text-sm text-slate-500 dark:text-white/40">{{ $stats['subscribed'] }} sekolah berlangganan</span>
        </div>
        <div class="space-y-4">
            @foreach ($planStats as $plan)
                @php $pct = $stats['total'] > 0 ? round($plan['count'] / $stats['total'] * 100) : 0; @endphp
                <div>
                    <div class="flex items-center justify-between text-sm mb-1">
                        <div class="flex items-center gap-2">
                            <span class="font-medium">{{ $plan['label'] }}</span>
                            <span class="text-xs text-slate-500 dark:text-white/40">
                                {{ $plan['price'] > 0 ? 'Rp '.number_format($plan['price'], 0, ',', '.') : 'Gratis' }}
                                @if ($plan['price'] > 0) {{ config('plans.plans.'.$plan['key'].'.period') }} @endif
                            </span>
                        </div>
                        <span class="font-semibold">
                            {{ $plan['count'] }} <span class="text-xs font-normal text-slate-500 dark:text-white/40">({{ $pct }}%)</span>
                        </span>
                    </div>
                    <div class="h-2 rounded-full bg-slate-100 dark:bg-white/5 overflow-hidden">
                        <div class="h-full rounded-full bg-primary/70" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6">
        <h2 class="text-lg font-semibold mb-4">Perlu Perhatian</h2>

        @forelse ($attention as $school)
            <a href="{{ route('platform.schools.show', $school) }}" class="block py-3 border-b border-slate-100 dark:border-white/5 last:border-0 hover:bg-slate-50 dark:hover:bg-white/5 -mx-2 px-2 rounded-lg group">
                <div class="flex items-center justify-between gap-2">
                    <span class="font-medium text-sm group-hover:text-primary">{{ $school->name }}</span>
                    <span class="rounded-full px-2 py-0.5 text-xs whitespace-nowrap
                        {{ $school->isExpired() ? 'bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-white/50' : '' }}
                        {{ $school->isTrial() ? 'bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400' : '' }}
                        {{ $school->isActive() && ! $school->isExpired() ? 'bg-rose-100 dark:bg-red-500/10 text-rose-700 dark:text-red-400' : '' }}">
                        {{ $school->status }}
                    </span>
                </div>
                <p class="mt-1 text-xs text-slate-500 dark:text-white/40">{{ $school->attentionReason() }}</p>
            </a>
        @empty
            <p class="text-sm text-slate-500 dark:text-white/40">Tidak ada sekolah yang perlu perhatian.</p>
        @endforelse
    </div>
</div>

<div class="mt-6 rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold">Sekolah Terbaru</h2>
        <a href="{{ route('platform.schools.index') }}" class="text-sm text-primary dark:text-primary hover:underline">Lihat semua</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead>
                <tr class="border-b border-slate-200 dark:border-white/10 text-left text-slate-500 dark:text-white/40">
                    <th class="py-2 pr-4">Nama</th>
                    <th class="py-2 pr-4">Paket</th>
                    <th class="py-2 pr-4">Status</th>
                    <th class="py-2">Didaftarkan</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($recentSchools as $school)
                    <tr class="border-b border-slate-100 dark:divide-white/5">
                        <td class="py-2 pr-4">
                            <a href="{{ route('platform.schools.show', $school) }}" class="text-primary dark:text-primary hover:underline">{{ $school->name }}</a>
                        </td>
                        <td class="py-2 pr-4">{{ $school->planLabel() }}</td>
                        <td class="py-2 pr-4">
                            <span class="rounded-full px-2 py-0.5 text-xs
                                {{ $school->status === 'active' ? 'bg-primary/15 dark:bg-primary/100/10 text-primary dark:text-primary' : '' }}
                                {{ $school->status === 'trial' ? 'bg-amber-100 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400' : '' }}
                                {{ $school->status === 'suspended' ? 'bg-rose-100 dark:bg-red-500/10 text-rose-700 dark:text-red-400' : '' }}
                                {{ $school->status === 'expired' ? 'bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-white/50' : '' }}">
                                {{ $school->status }}
                            </span>
                        </td>
                        <td class="py-2">{{ $school->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-4 text-center text-slate-500 dark:text-white/40">Belum ada sekolah terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection