@if (\App\Models\PlatformSetting::showSection('footer'))
    @php
        $medsosList = \App\Models\PlatformSetting::footerMedsos();
        $medsosIcons = config('platform.medsos_icons');
        $footerSetting = \App\Models\PlatformSetting::current();
    @endphp
    <footer class="border-t border-slate-100 bg-white">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid gap-10 md:grid-cols-[1.4fr_1fr_1fr]">
                {{-- Brand & Media Sosial --}}
                <div>
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <img src="{{ \App\Models\PlatformSetting::logo() ? asset('storage/' . \App\Models\PlatformSetting::logo()) : asset('images/logo.svg') }}" alt="Logo" class="h-9 w-9">
                        <div>
                            <p class="text-sm font-bold leading-tight text-primary-dark">{{ \App\Models\PlatformSetting::appName() }}</p>
                            <p class="text-xs text-slate-500">Sekolah Islam Terpadu</p>
                        </div>
                    </a>
                    <p class="mt-4 max-w-sm text-sm leading-relaxed text-slate-500">{{ \App\Models\PlatformSetting::footerTagline() }}</p>
                    @if ($medsosList !== [])
                        <div class="mt-5 flex items-center gap-3">
                            @foreach ($medsosList as $medsos)
                                @php
                                    $iconPath = $medsosIcons[$medsos['icon']] ?? null;
                                    $medsosUrl = $medsos['url'] ?? '';
                                    $medsosLabel = $medsos['label'] ?? '';
                                    $medsosClass = 'flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition hover:bg-primary hover:text-white';
                                @endphp
                                @if ($iconPath)
                                    @if ($medsosUrl)
                                        <a href="{{ $medsosUrl }}" target="_blank" rel="noopener" class="{{ $medsosClass }}" title="{{ $medsosLabel }}" aria-label="{{ $medsosLabel }}">
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="{{ $iconPath }}"/></svg>
                                        </a>
                                    @else
                                        <span class="{{ $medsosClass }}" title="{{ $medsosLabel }}" aria-label="{{ $medsosLabel }}">
                                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="{{ $iconPath }}"/></svg>
                                        </span>
                                    @endif
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Navigasi --}}
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">Navigasi</h3>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        <li><a href="{{ url('/#fitur') }}" class="text-slate-500 transition hover:text-primary">Fitur</a></li>
                        <li><a href="{{ url('/#tampilan') }}" class="text-slate-500 transition hover:text-primary">Tampilan</a></li>
                        <li><a href="{{ url('/#harga') }}" class="text-slate-500 transition hover:text-primary">Paket Harga</a></li>
                        <li><a href="{{ url('/blog') }}" class="text-slate-500 transition hover:text-primary">Blog & Artikel</a></li>
                        <li><a href="{{ url('/#faq') }}" class="text-slate-500 transition hover:text-primary">FAQ</a></li>
                    </ul>
                </div>

                {{-- Kontak --}}
                <div>
                    <h3 class="text-sm font-semibold text-slate-800">Kontak</h3>
                    <ul class="mt-4 space-y-3 text-sm text-slate-500">
                        @if ($footerSetting->footer_address)
                            <li class="flex items-start gap-2.5">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                                </svg>
                                <span>{{ $footerSetting->footer_address }}</span>
                            </li>
                        @endif
                        @if ($footerSetting->footer_phone)
                            <li class="flex items-start gap-2.5">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
                                </svg>
                                <a href="tel:{{ preg_replace('/[^+0-9]/', '', $footerSetting->footer_phone) }}" class="transition hover:text-primary">{{ $footerSetting->footer_phone }}</a>
                            </li>
                        @endif
                        @if ($footerSetting->footer_email)
                            <li class="flex items-start gap-2.5">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-primary" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                                </svg>
                                <a href="mailto:{{ $footerSetting->footer_email }}" class="transition hover:text-primary">{{ $footerSetting->footer_email }}</a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            <div class="mt-10 border-t border-slate-100 pt-6 text-center">
                <p class="text-sm text-slate-500">{!! \App\Models\PlatformSetting::footerCopyright() !!}</p>
            </div>
        </div>
    </footer>
@endif