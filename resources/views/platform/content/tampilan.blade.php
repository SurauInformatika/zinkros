@extends('layouts.app')

@section('title', 'Tampilan Aplikasi')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Tampilan Aplikasi</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Kelola judul section dan kartu mock-up aplikasi pada beranda.</p>
</div>

@include('platform.partials.form-alert')

<form method="POST" action="{{ route('platform.content.tampilan.update') }}" enctype="multipart/form-data" class="max-w-2xl space-y-6">
    @csrf
    @method('PUT')

    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6 sm:p-8">
        <div class="space-y-3 mb-6">
            <div>
                <label for="tampilan_heading" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Judul Section</label>
                <input id="tampilan_heading" type="text" name="tampilan_heading" value="{{ old('tampilan_heading', $settings->tampilan_heading) }}" maxlength="150"
                    class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('tampilan_heading') border-red-400 @enderror">
            </div>
            <div>
                <label for="tampilan_subtitle" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Subtitle Section</label>
                <input id="tampilan_subtitle" type="text" name="tampilan_subtitle" value="{{ old('tampilan_subtitle', $settings->tampilan_subtitle) }}" maxlength="300"
                    class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('tampilan_subtitle') border-red-400 @enderror">
            </div>
        </div>

        @php
            $oldShots = old('tampilan_shots');
            $oldShotList = is_array($oldShots) ? array_values($oldShots) : null;
            $shotSource = $oldShotList ?? \App\Models\PlatformSetting::shots();
            $shotCount = count($shotSource);
            $shotRows = [];
            for ($i = 0; $i < $shotCount; $i++) {
                $shotRows[] = [
                    'index' => $i,
                    'icon' => $oldShotList[$i]['icon'] ?? $shotSource[$i]['icon'] ?? 'data-master',
                    'label' => $oldShotList[$i]['label'] ?? $shotSource[$i]['label'] ?? '',
                    'caption' => $oldShotList[$i]['caption'] ?? $shotSource[$i]['caption'] ?? '',
                    'image' => $oldShotList[$i]['image'] ?? $shotSource[$i]['image'] ?? null,
                ];
            }
        @endphp

        <div id="shot-rows" class="space-y-4">
            @foreach ($shotRows as $shot)
                @include('platform.partials.shot-row', ['shot' => $shot, 'index' => $shot['index']])
            @endforeach
        </div>

        <button type="button" id="shot-add"
            class="mt-4 inline-flex items-center gap-2 rounded-xl border border-dashed border-slate-300 dark:border-white/15 px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-white/50 transition hover:border-primary hover:text-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah Kartu
        </button>
        @error('tampilan_shots')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror

        <label class="mt-6 flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 p-4">
            <span>
                <span class="block text-sm font-semibold text-slate-700 dark:text-white/70">Tampilkan di landing page</span>
                <span class="block text-xs text-slate-500 dark:text-white/40 mt-0.5">Jika dinonaktifkan, section Tampilan Aplikasi disembunyikan dari halaman beranda.</span>
            </span>
            <input type="checkbox" name="show_tampilan" value="1" @checked(old('show_tampilan', (bool) $settings->show_tampilan))
                class="h-5 w-5 rounded border-slate-300 text-primary focus:ring-primary">
        </label>
    </div>

    <div class="flex items-center gap-3 pb-8">
        <button type="submit"
            class="rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
            Simpan Tampilan
        </button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const shotRows = document.getElementById('shot-rows');
        const shotAdd = document.getElementById('shot-add');

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str == null ? '' : str;
            return div.innerHTML;
        }

        const featureIconOptions = {!! json_encode(
            collect(array_keys(config('platform.feature_icons')))
                ->map(fn ($key) => '<option value="' . e($key) . '">' . e(ucwords(str_replace('-', ' ', $key))) . '</option>')
                ->join('')
        ) !!};

        function iconOptions(selected) {
            if (!selected) return featureIconOptions;
            return featureIconOptions.replaceAll('value="' + selected + '"', 'value="' + selected + '" selected');
        }

        function nextShotIndex() {
            let max = -1;
            shotRows.querySelectorAll('[name^="tampilan_shots["]').forEach((el) => {
                const m = el.name.match(/\[(\d+)\]\[/);
                if (m) max = Math.max(max, parseInt(m[1], 10));
            });
            return max + 1;
        }

        function shotCardHtml(index) {
            return '<div class="shot-row rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 p-4">' +
                '<div class="flex items-start justify-between gap-3">' +
                    '<div class="flex flex-1 flex-wrap gap-3">' +
                        '<div>' +
                            '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Ikon</label>' +
                            '<select name="tampilan_shots[' + index + '][icon]" class="w-40 rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm outline-none transition">' + iconOptions('data-master') + '</select>' +
                        '</div>' +
                        '<div class="flex-1 min-w-[200px]">' +
                            '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Label (judul bar)</label>' +
                            '<input type="text" name="tampilan_shots[' + index + '][label]" maxlength="100" placeholder="mis. Dashboard Murid" class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm outline-none transition">' +
                        '</div>' +
                    '</div>' +
                    '<button type="button" class="shot-remove mt-6 rounded-lg p-1.5 text-slate-400 dark:text-white/30 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10 dark:hover:text-red-400" title="Hapus kartu">' +
                        '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>' +
                    '</button>' +
                '</div>' +
                '<div class="mt-3">' +
                    '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Caption</label>' +
                    '<input type="text" name="tampilan_shots[' + index + '][caption]" maxlength="200" placeholder="mis. Dashboard untuk murid" class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm outline-none transition">' +
                '</div>' +
                '<div class="mt-3">' +
                    '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Gambar (screenshot)</label>' +
                    '<div class="shot-dropzone relative flex flex-col items-center justify-center w-full h-44 rounded-xl border-2 border-dashed border-slate-300 dark:border-white/15 bg-white dark:bg-[#0a0a0a] cursor-pointer transition-all duration-200 hover:border-primary dark:hover:border-primary/40 group overflow-hidden">' +
                        '<input type="file" name="tampilan_shots[' + index + '][image]" accept="image/png,image/jpeg,image/webp" class="shot-file absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">' +
                        '<div class="shot-placeholder pointer-events-none relative z-10 text-center">' +
                            '<svg class="w-10 h-10 mx-auto mb-2 text-slate-400 dark:text-white/25 group-hover:text-primary dark:group-hover:text-primary transition" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>' +
                            '<p class="text-sm font-medium text-slate-600 dark:text-white/50 group-hover:text-primary dark:group-hover:text-primary transition"><span class="font-semibold">Klik untuk upload</span> screenshot</p>' +
                            '<p class="mt-1 text-xs text-slate-400 dark:text-white/30">PNG, JPG, atau WEBP. Maks 1MB.</p>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
        }

        function handleShotFile(input) {
            const file = input.files[0];
            if (!file) return;
            const drop = input.closest('.shot-dropzone');
            const placeholder = drop.querySelector('.shot-placeholder');
            if (placeholder) placeholder.classList.add('hidden');
            drop.querySelectorAll('.shot-preview, .shot-remove-image').forEach((el) => el.remove());
            const img = document.createElement('img');
            img.className = 'shot-preview absolute inset-0 w-full h-full object-cover object-top';
            img.alt = 'Screenshot';
            img.src = URL.createObjectURL(file);
            drop.prepend(img);
        }

        function renumberShotRows() {
            shotRows.querySelectorAll('.shot-row').forEach((row, idx) => {
                row.querySelectorAll('[name^="tampilan_shots["]').forEach((el) => {
                    el.name = el.name.replace(/^tampilan_shots\[\d+\]/, 'tampilan_shots[' + idx + ']');
                });
            });
        }

        shotAdd.addEventListener('click', () => {
            shotRows.insertAdjacentHTML('beforeend', shotCardHtml(nextShotIndex()));
        });

        shotRows.addEventListener('click', (e) => {
            const btn = e.target.closest('.shot-remove');
            if (btn) {
                btn.closest('.shot-row').remove();
                renumberShotRows();
            }
        });

        shotRows.addEventListener('change', (e) => {
            if (e.target.classList.contains('shot-file')) handleShotFile(e.target);
        });
    });
</script>
@endsection