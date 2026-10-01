@extends('layouts.app')

@section('title', 'Edit Guru')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.guru.index') }}" class="text-sm text-primary dark:text-primary hover:underline">&larr; Kembali ke daftar guru</a>
    <h1 class="mt-2 text-2xl font-bold">Edit Guru</h1>
</div>

<div class="max-w-2xl rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6 sm:p-8">
    <form method="POST" action="{{ route('admin.guru.update', $guru) }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Nama Lengkap</label>
            <input id="name" type="text" name="name" value="{{ old('name', $guru->name) }}" required autofocus
                class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none @error('name') border-red-400 @enderror">
            @error('name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid gap-5 sm:grid-cols-3">
            <div>
                <label for="gender" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Jenis Kelamin</label>
                <select id="gender" name="gender"
                    class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none @error('gender') border-red-400 @enderror">
                    <option value="">— Pilih —</option>
                    <option value="L" {{ old('gender', $guru->gender) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ old('gender', $guru->gender) === 'P' ? 'selected' : '' }}>Perempuan</option>
                </select>
                @error('gender')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $guru->email) }}" required
                    class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none @error('email') border-red-400 @enderror">
                @error('email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Telepon</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone', $guru->phone) }}"
                    class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none @error('phone') border-red-400 @enderror">
                @error('phone')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Password Baru <span class="text-xs text-slate-400 dark:text-white/30">(kosongkan jika tidak diubah)</span></label>
                <input id="password" type="password" name="password" autocomplete="new-password"
                    class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none @error('password') border-red-400 @enderror">
                @error('password')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Konfirmasi Password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password"
                    class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
            </div>
        </div>

        <div class="space-y-3 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 p-4">
            <p class="text-sm font-semibold text-slate-700 dark:text-white/70">Mata Pelajaran yang Diampu</p>
            @php($selectedSubjectIds = (array) old('subject_ids', $guru->subjects->pluck('id')->all()))
            @if ($subjects->isNotEmpty())
                <div class="grid gap-2 sm:grid-cols-2">
                    @foreach ($subjects as $subject)
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="subject_ids[]" value="{{ $subject->id }}"
                                {{ in_array($subject->id, $selectedSubjectIds, true) ? 'checked' : '' }}
                                class="rounded border-slate-300 text-primary focus:ring-primary">
                            <span class="ml-2 text-sm text-slate-600 dark:text-white/50">{{ $subject->name }}
                                @if ($subject->isQuran())
                                    <span class="text-xs text-purple-600 dark:text-purple-400">(Quran)</span>
                                @endif
                            </span>
                        </label>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-slate-500 dark:text-white/30">Belum ada mata pelajaran. Tambahkan dulu di menu Manajemen Mapel.</p>
            @endif
            @error('subject_ids')
                <p class="text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-3 rounded-xl bg-primary/10 dark:bg-primary/100/5 border border-primary/20 dark:border-primary/20 p-4">
            <p class="text-sm font-semibold text-slate-700 dark:text-white/70">Wali Kelas</p>
            <label class="inline-flex items-center">
                <input type="checkbox" name="is_wali" id="is_wali" value="1" {{ $isWali ? 'checked' : '' }}
                    class="rounded border-slate-300 text-primary focus:ring-primary">
                <span class="ml-2 text-sm text-slate-600 dark:text-white/50">Jadikan sebagai Wali Kelas</span>
            </label>
            <div id="wali-fields" class="hidden grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="wali_class_id" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Kelas</label>
                    <select id="wali_class_id" name="wali_class_id"
                        class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none @error('wali_class_id') border-red-400 @enderror">
                        <option value="">— Pilih Kelas —</option>
                        @foreach ($classes as $class)
                            <option value="{{ $class->id }}" {{ $waliClassId == $class->id ? 'selected' : '' }}>{{ $class->class_name }}</option>
                        @endforeach
                    </select>
                    @error('wali_class_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="wali_sort" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Posisi</label>
                    <select id="wali_sort" name="wali_sort"
                        class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none @error('wali_sort') border-red-400 @enderror">
                        <option value="">— Pilih —</option>
                        <option value="1" {{ $waliSort == 1 ? 'selected' : '' }}>Wali 1</option>
                        <option value="2" {{ $waliSort == 2 ? 'selected' : '' }}>Wali 2</option>
                    </select>
                    @error('wali_sort')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <p class="text-xs text-slate-400 dark:text-white/30">Tiap kelas maksimal 2 wali (Wali 1 &amp; Wali 2).</p>
        </div>

        <div class="space-y-3 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 p-4">
            <div class="flex items-center justify-between">
                <p class="text-sm font-semibold text-slate-700 dark:text-white/70">PJ / Penanggung Jawab</p>
                <button type="button" onclick="addRole()" class="text-xs text-primary hover:text-primary font-medium">+ Tambah Role</button>
            </div>
            @php($otherRoles = $existingRoles->filter(fn ($info, $name) => !$guru->teacherRoles->contains('role_name', $name)))
            @if ($otherRoles->isNotEmpty())
                <div class="rounded-lg bg-white dark:bg-[#0a0a0a] border border-slate-200 dark:border-white/5 p-3">
                    <p class="text-xs font-medium text-slate-500 dark:text-white/40 mb-2">PJ lain yang sudah ada:</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($otherRoles as $roleName => $info)
                            <span class="inline-flex items-center gap-1 rounded-full bg-purple-50 dark:bg-purple-500/10 px-2.5 py-1 text-xs font-medium text-purple-700 dark:text-purple-400">
                                {{ $roleName }}
                                <span class="rounded-full bg-purple-200 dark:bg-purple-500/20 px-1.5 text-[10px] font-bold">{{ $info['count'] }}</span>
                                <span class="text-purple-400 dark:text-purple-500/50 text-[10px]">{{ $info['teachers']->implode(', ') }}</span>
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
            <div id="roles-container" class="space-y-2">
            </div>
            <p class="text-xs text-slate-400 dark:text-white/30">PJ Tahfidz, PJ Sarana, PJ Laboratorium, dll. Centang "Menangani Murid" jika role ini berhubungan dengan aktivitas siswa.</p>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
                Simpan Perubahan
            </button>
            <a href="{{ route('admin.guru.index') }}" class="text-sm text-slate-600 dark:text-white/50 hover:text-slate-800">Batal</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        @foreach ($guru->teacherRoles as $role)
            addRole('{{ addslashes($role->role_name) }}', {{ $role->is_student_related ? 'true' : 'false' }});
        @endforeach
    });

    let roleIndex = 0;

    const waliCheckbox = document.getElementById('is_wali');
    const waliFields = document.getElementById('wali-fields');
    function toggleWaliFields() {
        waliFields.classList.toggle('hidden', !waliCheckbox.checked);
    }
    if (waliCheckbox) {
        waliCheckbox.addEventListener('change', toggleWaliFields);
        toggleWaliFields();
    }

    function addRole(name = '', isStudentRelated = false) {
        const container = document.getElementById('roles-container');
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2';
        div.innerHTML = `
            <input type="text" name="role_names[]" value="${name}" placeholder="Contoh: PJ Tahfidz"
                class="flex-1 rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none">
            <label class="inline-flex items-center whitespace-nowrap">
                <input type="checkbox" name="role_student_related[]" value="${roleIndex}"
                    ${isStudentRelated ? 'checked' : ''}
                    class="rounded border-slate-300 text-primary focus:ring-primary">
                <span class="ml-1 text-xs text-slate-600 dark:text-white/50">Menangani Murid</span>
            </label>
            <button type="button" onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600 text-sm">&times;</button>
        `;
        container.appendChild(div);
        roleIndex++;
    }
</script>
@endpush
