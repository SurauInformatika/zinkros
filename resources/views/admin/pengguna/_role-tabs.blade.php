@php
    $school = auth()->user()->school;
    $tabRoles = $school?->penggunaRoles() ?? [];
    $studentCount = $school ? $school->students()->count() : 0;
    $roleCounts = \App\Models\User::query()
        ->when($school, fn ($q) => $q->where('school_id', $school->id))
        ->whereIn('role', $tabRoles)
        ->selectRaw('role, COUNT(*) AS total')
        ->groupBy('role')
        ->pluck('total', 'role')
        ->all();

    $wakasekGroup = \App\Http\Controllers\Admin\PenggunaController::WAKASEK_GROUP;

    $tabHrefs = [];
    foreach ($tabRoles as $key) {
        $tabHrefs[$key] = $key === 'guru'
            ? route('admin.guru.index')
            : ($key === 'staff' ? route('admin.staff.index') : route('admin.pengguna.index', ['role' => $key]));
    }

    $activeLabelKey = $activeRole === $wakasekGroup || $activeRole === \App\Models\User::ROLE_WAKASEK
        ? $wakasekGroup
        : $activeRole;
@endphp

<div class="flex gap-1 mb-4 p-1 bg-slate-100 dark:bg-white/5 rounded-xl w-fit overflow-x-auto max-w-full">
    @forelse ($tabHrefs as $key => $href)
        <a href="{{ $href }}"
            class="whitespace-nowrap rounded-lg px-4 py-2 text-sm font-semibold transition-all duration-150
                {{ $activeLabelKey === $key ? 'bg-white dark:bg-[#141414] text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 dark:text-white/40 hover:text-slate-900 dark:hover:text-white' }}">
            {{ $school->roleLabel($key) }}
            ({{ $roleCounts[$key] ?? 0 }})
        </a>
    @empty
        <p class="px-4 py-1 text-sm text-slate-400 dark:text-white/30">Tidak ada role yang aktif. Aktifkan di Pengaturan Sekolah.</p>
    @endforelse
    <span class="mx-1 my-1 w-px bg-slate-300 dark:bg-white/15 shrink-0"></span>
    <a href="{{ route('admin.siswa.index') }}"
        class="whitespace-nowrap rounded-lg px-4 py-2 text-sm font-semibold transition-all duration-150
            {{ $activeRole === 'siswa' ? 'bg-white dark:bg-[#141414] text-slate-900 dark:text-white shadow-sm' : 'text-slate-500 dark:text-white/40 hover:text-slate-900 dark:hover:text-white' }}">
        Siswa ({{ $studentCount }})
    </a>
</div>
