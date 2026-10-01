@extends('layouts.app')

@section('title', 'Edit Siswa')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.siswa.index') }}" class="text-sm text-primary dark:text-primary hover:underline">&larr; Kembali ke daftar siswa</a>
    <h1 class="mt-2 text-2xl font-bold">Edit Siswa</h1>
</div>

<div class="max-w-xl rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6 sm:p-8">
    <form method="POST" action="{{ route('admin.siswa.update', $student) }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Nama Lengkap</label>
            <input id="name" type="text" name="name" value="{{ old('name', $student->name) }}" required autofocus
                class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none @error('name') border-red-400 @enderror">
            @error('name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="gender" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Jenis Kelamin</label>
            <select id="gender" name="gender" required
                class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none @error('gender') border-red-400 @enderror">
                <option value="">-- Pilih --</option>
                <option value="L" {{ old('gender', $student->gender) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                <option value="P" {{ old('gender', $student->gender) == 'P' ? 'selected' : '' }}>Perempuan</option>
            </select>
            @error('gender')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="nisn" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">NISN <span class="text-slate-400">(opsional)</span></label>
            <input id="nisn" type="text" name="nisn" value="{{ old('nisn', $student->nisn) }}"
                class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none @error('nisn') border-red-400 @enderror">
            @error('nisn')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="nis" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">NIS</label>
            <input id="nis" type="text" name="nis" value="{{ old('nis', $student->nis) }}" required
                class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none @error('nis') border-red-400 @enderror">
            @error('nis')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="rfid_tag_id" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">RFID Tag ID <span class="text-slate-400">(opsional)</span></label>
            <input id="rfid_tag_id" type="text" name="rfid_tag_id" value="{{ old('rfid_tag_id', $student->rfid_tag_id) }}"
                placeholder="Untuk absensi kartu"
                class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none @error('rfid_tag_id') border-red-400 @enderror">
            @error('rfid_tag_id')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="class_id" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Kelas</label>
            <select id="class_id" name="class_id" required
                class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none @error('class_id') border-red-400 @enderror">
                <option value="">-- Pilih Kelas --</option>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}" {{ old('class_id', $student->class_id) == $class->id ? 'selected' : '' }}>
                        {{ $class->class_name }}
                    </option>
                @endforeach
            </select>
            @error('class_id')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        @include('admin.student._parents')

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                class="rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
                Perbarui Siswa
            </button>
            <a href="{{ route('admin.siswa.index') }}" class="text-sm text-slate-600 dark:text-white/50 hover:text-slate-800">Batal</a>
        </div>
    </form>
</div>
@endsection
