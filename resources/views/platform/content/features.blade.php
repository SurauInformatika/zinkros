@extends('layouts.app')

@section('title', 'Fitur Utama')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Fitur Utama</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Kelola judul section dan kartu fitur pada beranda. Bisa tambah, hapus, edit, dan pilih ikon.</p>
</div>

@include('platform.partials.form-alert')

<form method="POST" action="{{ route('platform.content.features.update') }}" class="max-w-2xl space-y-6">
    @csrf
    @method('PUT')

    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6 sm:p-8">
        <div class="space-y-3 mb-6">
            <div>
                <label for="fitur_heading" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Judul Section</label>
                <input id="fitur_heading" type="text" name="fitur_heading" value="{{ old('fitur_heading', $settings->fitur_heading) }}" maxlength="100"
                    class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('fitur_heading') border-red-400 @enderror">
            </div>
            <div>
                <label for="fitur_subtitle" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Subtitle Section</label>
                <input id="fitur_subtitle" type="text" name="fitur_subtitle" value="{{ old('fitur_subtitle', $settings->fitur_subtitle) }}" maxlength="255"
                    class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('fitur_subtitle') border-red-400 @enderror">
            </div>
        </div>

        @php
            $oldFeatures = old('features');
            $oldFeatureList = is_array($oldFeatures) ? array_values($oldFeatures) : null;
            $decodedFeatures = json_decode($settings->features ?? '', true);
            $featureSource = $oldFeatureList ?? (is_array($decodedFeatures) && $decodedFeatures !== [] ? $decodedFeatures : config('platform.feature_defaults'));
            $featureCount = count($featureSource);
            $featureRows = [];
            for ($i = 0; $i < $featureCount; $i++) {
                $featureRows[] = [
                    'index' => $i,
                    'icon' => $oldFeatureList[$i]['icon'] ?? $featureSource[$i]['icon'] ?? 'absensi',
                    'title' => $oldFeatureList[$i]['title'] ?? $featureSource[$i]['title'] ?? '',
                    'desc' => $oldFeatureList[$i]['desc'] ?? $featureSource[$i]['desc'] ?? '',
                ];
            }
        @endphp

        <div id="feature-rows" class="space-y-4">
            @foreach ($featureRows as $feature)
                @include('platform.partials.feature-row', ['feature' => $feature, 'index' => $feature['index']])
            @endforeach
        </div>

        <button type="button" id="feature-add"
            class="mt-4 inline-flex items-center gap-2 rounded-xl border border-dashed border-slate-300 dark:border-white/15 px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-white/50 transition hover:border-primary hover:text-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah Fitur
        </button>
        @error('features')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror

        <label class="mt-6 flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 p-4">
            <span>
                <span class="block text-sm font-semibold text-slate-700 dark:text-white/70">Tampilkan di landing page</span>
                <span class="block text-xs text-slate-500 dark:text-white/40 mt-0.5">Jika dinonaktifkan, section Fitur Utama disembunyikan dari halaman beranda.</span>
            </span>
            <input type="checkbox" name="show_fitur" value="1" @checked(old('show_fitur', (bool) $settings->show_fitur))
                class="h-5 w-5 rounded border-slate-300 text-primary focus:ring-primary">
        </label>
    </div>

    <div class="flex items-center gap-3 pb-8">
        <button type="submit"
            class="rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
            Simpan Fitur
        </button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const featureRows = document.getElementById('feature-rows');
        const featureAdd = document.getElementById('feature-add');

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

        function nextFeatureIndex() {
            let max = -1;
            featureRows.querySelectorAll('[name^="features["]').forEach((el) => {
                const m = el.name.match(/\[(\d+)\]\[/);
                if (m) max = Math.max(max, parseInt(m[1], 10));
            });
            return max + 1;
        }

        function featureRowHtml(index, feature = { icon: 'absensi', title: '', desc: '' }) {
            return '<div class="feature-row rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 p-4">' +
                '<div class="flex items-start justify-between gap-3">' +
                    '<div class="flex flex-1 flex-wrap gap-3">' +
                        '<div>' +
                            '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Ikon</label>' +
                            '<select name="features[' + index + '][icon]" class="w-40 rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm outline-none transition">' + iconOptions(feature.icon) + '</select>' +
                        '</div>' +
                        '<div class="flex-1 min-w-[200px]">' +
                            '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Judul</label>' +
                            '<input type="text" name="features[' + index + '][title]" maxlength="100" value="' + escapeHtml(feature.title) + '" class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm outline-none transition">' +
                        '</div>' +
                    '</div>' +
                    '<button type="button" class="feature-remove mt-6 rounded-lg p-1.5 text-slate-400 dark:text-white/30 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10 dark:hover:text-red-400" title="Hapus fitur">' +
                        '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>' +
                    '</button>' +
                '</div>' +
                '<div class="mt-3">' +
                    '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Deskripsi</label>' +
                    '<textarea name="features[' + index + '][desc]" rows="2" maxlength="255" class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm outline-none transition resize-y">' + escapeHtml(feature.desc) + '</textarea>' +
                '</div>' +
            '</div>';
        }

        function renumberFeatureRows() {
            featureRows.querySelectorAll('.feature-row').forEach((row, idx) => {
                row.querySelectorAll('[name^="features["]').forEach((el) => {
                    el.name = el.name.replace(/^features\[\d+\]/, 'features[' + idx + ']');
                });
            });
        }

        featureAdd.addEventListener('click', () => {
            featureRows.insertAdjacentHTML('beforeend', featureRowHtml(nextFeatureIndex()));
        });

        featureRows.addEventListener('click', (e) => {
            const btn = e.target.closest('.feature-remove');
            if (btn) {
                btn.closest('.feature-row').remove();
                renumberFeatureRows();
            }
        });
    });
</script>
@endsection