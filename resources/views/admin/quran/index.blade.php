@extends('layouts.app')

@section('title', 'Master Quran')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold">Master Quran</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40 dark:text-white/40">Daftar 114 surah Al-Qur'an (data referensi).</p>
</div>

<div class="overflow-hidden rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10">
<div class="overflow-x-auto">    <table class="min-w-full divide-y divide-slate-200 text-sm whitespace-nowrap">
        <thead class="bg-slate-50 dark:bg-white/5 text-left text-xs uppercase text-slate-500 dark:text-white/40 dark:text-white/40">
            <tr>
                <th class="px-5 py-3 font-semibold w-16">No.</th>
                <th class="px-5 py-3 font-semibold">Nama Surah</th>
                <th class="px-5 py-3 font-semibold text-right w-28">Jumlah Ayat</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($surahs as $surah)
                <tr class="hover:bg-slate-50 dark:hover:bg-white/5">
                    <td class="px-5 py-2.5 text-slate-500 dark:text-white/40">{{ $surah->surah_number }}</td>
                    <td class="px-5 py-2.5 font-medium text-slate-900 dark:text-white">{{ $surah->surah_name }}</td>
                    <td class="px-5 py-2.5 text-right text-slate-600 dark:text-white/50">{{ $surah->total_ayats }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-5 py-10 text-center text-slate-500 dark:text-white/40">Belum ada data surah.</td>
                </tr>
            @endforelse
        </tbody>
    </table></div>
</div>
@endsection
