@extends('layouts.app')

@section('title', 'Sekolah & Testimoni')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold tracking-tight">Sekolah & Testimoni</h1>
    <p class="mt-1 text-sm text-slate-500 dark:text-white/40">Kelola judul section, testimoni, dan toggle tampilan pada beranda. Daftar sekolah pengguna diambil otomatis dari data sekolah.</p>
</div>

@include('platform.partials.form-alert')

<form method="POST" action="{{ route('platform.content.sekolah.update') }}" class="max-w-2xl space-y-6">
    @csrf
    @method('PUT')

    <div class="rounded-xl bg-white dark:bg-[#141414] border border-slate-200 dark:border-white/10 p-6 sm:p-8">
        <label class="mb-5 flex items-center justify-between rounded-xl border border-slate-200 bg-slate-50 p-4">
            <span>
                <span class="block text-sm font-semibold text-slate-700 dark:text-white/70">Tampilkan di landing page</span>
                <span class="block text-xs text-slate-500 dark:text-white/40 mt-0.5">Jika dinonaktifkan, bagian Sekolah & Testimoni disembunyikan dari halaman beranda.</span>
            </span>
            <input type="checkbox" name="show_sekolah" value="1" @checked(old('show_sekolah', (bool) $settings->show_sekolah))
                class="h-5 w-5 rounded border-slate-300 text-primary focus:ring-primary">
        </label>

        <div class="space-y-3 mb-6">
            <div>
                <label for="sekolah_heading" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Judul Section</label>
                <input id="sekolah_heading" type="text" name="sekolah_heading" value="{{ old('sekolah_heading', $settings->sekolah_heading) }}" maxlength="150"
                    class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('sekolah_heading') border-red-400 @enderror">
            </div>
            <div>
                <label for="sekolah_subtitle" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Subtitle Section</label>
                <input id="sekolah_subtitle" type="text" name="sekolah_subtitle" value="{{ old('sekolah_subtitle', $settings->sekolah_subtitle) }}" maxlength="300"
                    class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('sekolah_subtitle') border-red-400 @enderror">
            </div>
        </div>

        @php
            $oldTestimonials = old('testimonials');
            $oldTestimonialList = is_array($oldTestimonials) ? array_values($oldTestimonials) : null;
            $decodedTestimonials = json_decode($settings->testimonials ?? '', true);
            $testimonialSource = $oldTestimonialList ?? (is_array($decodedTestimonials) && $decodedTestimonials !== [] ? $decodedTestimonials : config('platform.testimonial_defaults'));
            $testimonialCount = count($testimonialSource);
            $testimonialRows = [];
            for ($i = 0; $i < $testimonialCount; $i++) {
                $testimonialRows[] = [
                    'index' => $i,
                    'quote' => $oldTestimonialList[$i]['quote'] ?? $testimonialSource[$i]['quote'] ?? '',
                    'author' => $oldTestimonialList[$i]['author'] ?? $testimonialSource[$i]['author'] ?? '',
                    'role' => $oldTestimonialList[$i]['role'] ?? $testimonialSource[$i]['role'] ?? '',
                    'initials' => $oldTestimonialList[$i]['initials'] ?? $testimonialSource[$i]['initials'] ?? '',
                ];
            }
        @endphp

        <div class="mb-6 flex items-center justify-between mt-8">
            <h2 class="text-sm font-semibold text-slate-700 dark:text-white/70">Testimoni</h2>
        </div>
        <div class="space-y-3 mb-3">
            <div>
                <label for="testi_heading" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Judul Testimoni</label>
                <input id="testi_heading" type="text" name="testi_heading" value="{{ old('testi_heading', $settings->testi_heading) }}" maxlength="150"
                    class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('testi_heading') border-red-400 @enderror">
            </div>
            <div>
                <label for="testi_subtitle" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Subtitle Testimoni</label>
                <input id="testi_subtitle" type="text" name="testi_subtitle" value="{{ old('testi_subtitle', $settings->testi_subtitle) }}" maxlength="300"
                    class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition @error('testi_subtitle') border-red-400 @enderror">
            </div>
        </div>

        <div id="testimonial-rows" class="space-y-4 mt-4">
            @foreach ($testimonialRows as $testimonial)
                @include('platform.partials.testimonial-row', ['testimonial' => $testimonial, 'index' => $testimonial['index']])
            @endforeach
        </div>

        <button type="button" id="testimonial-add"
            class="mt-4 inline-flex items-center gap-2 rounded-xl border border-dashed border-slate-300 dark:border-white/15 px-4 py-2.5 text-sm font-medium text-slate-600 dark:text-white/50 transition hover:border-primary hover:text-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Tambah Testimoni
        </button>
        @error('testimonials')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center gap-3 pb-8">
        <button type="submit"
            class="rounded-xl bg-gradient-to-r from-primary to-secondary px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition hover:shadow-xl hover:shadow-primary/30">
            Simpan
        </button>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const testimonialRows = document.getElementById('testimonial-rows');
        const testimonialAdd = document.getElementById('testimonial-add');

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str == null ? '' : str;
            return div.innerHTML;
        }

        function nextTestimonialIndex() {
            let max = -1;
            testimonialRows.querySelectorAll('[name^="testimonials["]').forEach((el) => {
                const m = el.name.match(/\[(\d+)\]\[/);
                if (m) max = Math.max(max, parseInt(m[1], 10));
            });
            return max + 1;
        }

        function testimonialRowHtml(index, t = { quote: '', author: '', role: '', initials: '' }) {
            return '<div class="testimonial-row rounded-xl border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/5 p-4">' +
                '<div class="flex items-start justify-between gap-3">' +
                    '<div class="flex-1 min-w-[200px]">' +
                        '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Kutipan Testimoni</label>' +
                        '<textarea name="testimonials[' + index + '][quote]" rows="3" maxlength="1000" class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm outline-none transition resize-y">' + escapeHtml(t.quote) + '</textarea>' +
                    '</div>' +
                    '<button type="button" class="testimonial-remove mt-6 rounded-lg p-1.5 text-slate-400 dark:text-white/30 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10 dark:hover:text-red-400" title="Hapus testimoni">' +
                        '<svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>' +
                    '</button>' +
                '</div>' +
                '<div class="mt-3 grid gap-3 sm:grid-cols-3">' +
                    '<div>' +
                        '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Nama</label>' +
                        '<input type="text" name="testimonials[' + index + '][author]" maxlength="100" value="' + escapeHtml(t.author) + '" class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm outline-none transition">' +
                    '</div>' +
                    '<div class="flex-1 min-w-[180px]">' +
                        '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Peran / Sekolah</label>' +
                        '<input type="text" name="testimonials[' + index + '][role]" maxlength="150" value="' + escapeHtml(t.role) + '" class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm outline-none transition">' +
                    '</div>' +
                    '<div>' +
                        '<label class="block text-xs font-medium text-slate-500 dark:text-white/40 mb-1">Inisial Avatar</label>' +
                        '<input type="text" name="testimonials[' + index + '][initials]" maxlength="4" value="' + escapeHtml(t.initials) + '" class="w-full rounded-lg border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-3 py-2 text-sm outline-none transition">' +
                    '</div>' +
                '</div>' +
            '</div>';
        }

        function renumberTestimonialRows() {
            testimonialRows.querySelectorAll('.testimonial-row').forEach((row, idx) => {
                row.querySelectorAll('[name^="testimonials["]').forEach((el) => {
                    el.name = el.name.replace(/^testimonials\[\d+\]/, 'testimonials[' + idx + ']');
                });
            });
        }

        testimonialAdd.addEventListener('click', () => {
            testimonialRows.insertAdjacentHTML('beforeend', testimonialRowHtml(nextTestimonialIndex()));
        });

        testimonialRows.addEventListener('click', (e) => {
            const btn = e.target.closest('.testimonial-remove');
            if (btn) {
                btn.closest('.testimonial-row').remove();
                renumberTestimonialRows();
            }
        });
    });
</script>
@endsection