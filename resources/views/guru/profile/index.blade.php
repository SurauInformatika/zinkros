@extends('layouts.app')

@section('title', 'Profil Saya')

@php
    $tabs = [
        ['route' => 'guru.profile.index', 'label' => 'Profil', 'active' => true],
        ['route' => 'guru.profile.password', 'label' => 'Password', 'active' => false],
    ];
@endphp

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Profil Saya</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Kelola data profil Anda.</p>
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

@if (session('status'))
<div class="max-w-2xl mb-4 rounded-xl bg-primary/10 dark:bg-primary/100/10 border border-primary/20 dark:border-primary/20 p-4 text-sm text-primary dark:text-primary">{{ session('status') }}</div>
@endif

<div class="max-w-2xl space-y-6">
    {{-- Profil --}}
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden">
        <div class="border-b border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.02] px-6 py-4">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-white/80">Profil</h2>
        </div>
        <div class="p-6">
            <div class="flex items-start gap-5 mb-6">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-primary to-secondary shadow-lg shadow-primary/25">
                    <span class="text-xl font-bold text-white">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold">{{ $user->name }}</h3>
                    <p class="text-sm text-slate-500 dark:text-white/40">{{ $user->email }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <span class="inline-flex items-center rounded-full bg-primary/10 dark:bg-primary/100/10 px-2.5 py-0.5 text-xs font-medium text-primary dark:text-primary">Aktif</span>
                        <span class="inline-flex items-center rounded-full bg-blue-50 dark:bg-blue-500/10 px-2.5 py-0.5 text-xs font-medium text-blue-700 dark:text-blue-400 capitalize">{{ $user->role }}</span>
                        @if ($user->isQuranTeacher())
                            <span class="inline-flex items-center rounded-full bg-purple-50 dark:bg-purple-500/10 px-2.5 py-0.5 text-xs font-medium text-purple-700 dark:text-purple-400">Guru Al-Quran</span>
                        @endif
                    </div>
                </div>
            </div>

            <form id="profileForm" method="POST" action="{{ route('guru.profile.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Nama Lengkap</label>
                        <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                            class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('name') border-red-400 @enderror">
                        @error('name')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                            class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('email') border-red-400 @enderror">
                        @error('email')
                            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Telepon</label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                        class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('phone') border-red-400 @enderror">
                    @error('phone')
                        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" id="profileBtn"
                        class="rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
                        Simpan Profil
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Penugasan --}}
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 overflow-hidden">
        <div class="border-b border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.02] px-6 py-4">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-white/80">Penugasan</h2>
        </div>
        <div class="p-6 space-y-5">
            {{-- Wali Kelas --}}
            <div>
                <h3 class="text-xs font-medium text-slate-500 dark:text-white/40 uppercase tracking-wider mb-2">Wali Kelas</h3>
                @if ($waliClasses->count() > 0)
                    <div class="flex flex-wrap gap-2">
                        @foreach ($waliClasses as $class)
                            <span class="inline-flex items-center gap-1.5 rounded-lg bg-primary/10 dark:bg-primary/100/10 px-3 py-1.5 text-sm font-medium text-primary dark:text-primary">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                {{ $class->class_name }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-400 dark:text-white/30">Tidak ada wali kelas</p>
                @endif
            </div>

            {{-- Mapel yang Diampu --}}
            <div>
                <h3 class="text-xs font-medium text-slate-500 dark:text-white/40 uppercase tracking-wider mb-2">Mata Pelajaran yang Diampu</h3>
                @if ($taughtSubjectsByClass->count() > 0)
                    <div class="space-y-2">
                        @foreach ($taughtSubjectsByClass as $item)
                            <div class="rounded-lg border border-slate-100 dark:border-white/5 p-3">
                                <p class="text-sm font-medium text-slate-700 dark:text-white/80">{{ $item['class_name'] }}</p>
                                <div class="mt-1.5 flex flex-wrap gap-1">
                                    @foreach ($item['subjects'] as $subject)
                                        <span class="inline-flex items-center rounded-md bg-blue-50 dark:bg-blue-500/10 px-2 py-0.5 text-xs font-medium text-blue-600 dark:text-blue-400">{{ $subject }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-400 dark:text-white/30">Tidak ada penugasan mengajar</p>
                @endif
            </div>

            {{-- PJ / Penanggung Jawab --}}
            @if ($user->teacherRoles->isNotEmpty())
            <div>
                <h3 class="text-xs font-medium text-slate-500 dark:text-white/40 uppercase tracking-wider mb-2">Penanggung Jawab</h3>
                @foreach ($user->teacherRoles as $role)
                    <div class="rounded-lg border border-purple-100 dark:border-purple-500/20 bg-purple-50 dark:bg-purple-500/5 p-3 mb-2">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center rounded-full bg-purple-100 dark:bg-purple-500/20 px-2 py-0.5 text-xs font-medium text-purple-700 dark:text-purple-400">{{ $role->role_name }}</span>
                            @if ($role->is_student_related)
                                <span class="text-xs text-slate-400 dark:text-white/30">Menangani Murid</span>
                            @endif
                        </div>
                        @if ($role->description)
                            <p class="text-sm text-slate-600 dark:text-white/60">{{ $role->description }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
            @endif

            {{-- Guru Al-Quran --}}
            @if ($user->isQuranTeacher())
            <div>
                <h3 class="text-xs font-medium text-slate-500 dark:text-white/40 uppercase tracking-wider mb-2">Guru Al-Quran</h3>
                <div class="rounded-lg border border-purple-100 dark:border-purple-500/20 bg-purple-50 dark:bg-purple-500/5 p-3">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <span class="text-sm font-medium text-purple-700 dark:text-purple-400">Mengajar Al-Quran</span>
                    </div>
                    @if ($quranAssignments->count() > 0)
                        <p class="text-sm text-slate-600 dark:text-white/60">{{ $quranAssignments->count() }} siswa ditugaskan</p>
                    @endif
                    @php
                        $quranSubjects = $user->subjects()->where('subjects.type', 'QURAN')->pluck('subjects.name');
                    @endphp
                    @if ($quranSubjects->isNotEmpty())
                        <div class="mt-2 flex flex-wrap gap-1">
                            @foreach ($quranSubjects as $subj)
                                <span class="inline-flex items-center rounded-md bg-purple-100 dark:bg-purple-500/20 px-2 py-0.5 text-xs font-medium text-purple-700 dark:text-purple-400">{{ $subj }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Statistik --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4 text-center">
            <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ $waliClasses->count() + $taughtSubjectsByClass->count() }}</p>
            <p class="text-xs text-slate-500 dark:text-white/40 mt-1">Kelas Diampu</p>
        </div>
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4 text-center">
            <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ $totalMapelSiswa }}</p>
            <p class="text-xs text-slate-500 dark:text-white/40 mt-1">Plotting Mapel</p>
        </div>
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4 text-center">
            <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ $totalTahfidzSiswa }}</p>
            <p class="text-xs text-slate-500 dark:text-white/40 mt-1">Siswa Tahfidz</p>
        </div>
        <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-4 text-center">
            <p class="text-2xl font-bold text-slate-800 dark:text-white">{{ $classSubjectTeachers->pluck('subject.name')->unique()->count() }}</p>
            <p class="text-xs text-slate-500 dark:text-white/40 mt-1">Mata Pelajaran</p>
        </div>
    </div>
</div>
@endsection