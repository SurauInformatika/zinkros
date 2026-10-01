@extends('layouts.app')

@section('title', 'Plotting Mapel')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Plotting Mapel / Halqah</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Tentukan guru pengampu per mapel di setiap kelas.</p>
</div>

<div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
    @forelse ($classes as $class)
        <a href="{{ route('admin.plotting.edit', $class) }}"
            class="group rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-5 transition-all duration-200 hover:border-primary/30 dark:hover:border-primary/30 hover:shadow-lg hover:shadow-primary/5">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-base font-bold">{{ $class->class_name }}</h3>
                <svg class="w-4 h-4 text-slate-400 dark:text-white/30 group-hover:text-primary transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </div>
            <p class="text-sm text-slate-500 dark:text-white/40">{{ $class->subjects_count ?? 0 }} mapel diplotting</p>
        </a>
    @empty
        <div class="col-span-full rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 px-5 py-10 text-center text-slate-500 dark:text-white/30">
            Belum ada kelas.
        </div>
    @endforelse
</div>
@endsection
