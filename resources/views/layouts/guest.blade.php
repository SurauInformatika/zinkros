<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', \App\Models\PlatformSetting::appName())</title>
    @php $favicon = \App\Models\PlatformSetting::logo(); @endphp
    @if ($favicon)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $favicon) }}">
    @endif
    @include('partials._vite')
    <style>
        :root {
            --color-primary: {{ \App\Models\PlatformSetting::primaryColor() }};
            --color-secondary: {{ \App\Models\PlatformSetting::secondaryColor() }};
            --color-primary-dark: {{ \App\Models\PlatformSetting::darken(\App\Models\PlatformSetting::primaryColor(), 0.88) }};
            --color-secondary-dark: {{ \App\Models\PlatformSetting::darken(\App\Models\PlatformSetting::secondaryColor(), 0.88) }};
        }
    </style>
</head>
<body class="font-sans antialiased bg-slate-50 dark:bg-[#0a0a0a] text-slate-900 dark:text-slate-100 transition-colors duration-200">
    <script>
        (function() {
            var saved = localStorage.getItem('theme');
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (saved === 'dark' || (!saved && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <main>
        @yield('content')
    </main>
    <script>
    (function() {
        var spinner = '<svg class="animate-spin h-4 w-4 inline mr-1.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>';
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('submit', function() {
                var btn = form.querySelector('button[type="submit"]');
                if (!btn || btn.disabled) return;
                btn.disabled = true;
                btn.dataset.originalHtml = btn.innerHTML;
                btn.innerHTML = spinner + ' Mengirim...';
            });
        });
        window.addEventListener('pageshow', function() {
            document.querySelectorAll('button[disabled]').forEach(function(btn) {
                if (btn.dataset.originalHtml) {
                    btn.disabled = false;
                    btn.innerHTML = btn.dataset.originalHtml;
                    delete btn.dataset.originalHtml;
                }
            });
        });
    })();
    </script>
</body>
</html>
