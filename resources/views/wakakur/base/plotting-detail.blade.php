@extends('layouts.app')

@section('title', 'Plotting: ' . $class->class_name)

@section('content')
<div class="mb-6 flex items-center gap-3">
    <a href="{{ route($routeGroup . '.teachers', ['view' => 'kelas']) }}" class="text-slate-400 hover:text-slate-600 dark:hover:text-white/60">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <div>
        <h1 class="text-2xl font-bold tracking-tight">Plotting: {{ $class->class_name }}</h1>
        <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Wali Kelas: {{ $class->waliNames() }}</p>
    </div>
</div>

<div class="space-y-4">
    @forelse ($plotting as $subjectName => $items)
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5">
        <h3 class="font-semibold text-slate-800 dark:text-white/80 mb-2">{{ $subjectName }}</h3>
        <div class="flex flex-wrap gap-2">
            @foreach ($items as $item)
                <span class="inline-flex items-center rounded-full bg-primary/10 dark:bg-primary/100/10 px-3 py-1 text-xs font-medium text-primary dark:text-primary">
                    {{ $item->teacher->name ?? '???' }}
                </span>
            @endforeach
        </div>
    </div>
    @empty
    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-8 text-center text-slate-400 dark:text-white/30 text-sm">
        Belum ada plotting untuk kelas ini.
    </div>
    @endforelse
</div>
@endsection
