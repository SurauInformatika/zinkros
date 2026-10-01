@php
    $viteManifest = null;
    $viteProbes = [
        public_path('build/manifest.json'),
        (($_SERVER['DOCUMENT_ROOT'] ?? '') ? rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . '/build/manifest.json' : ''),
        base_path('public/build/manifest.json'),
    ];
    $viteChecked = [];
    foreach ($viteProbes as $viteProbe) {
        if (!$viteProbe || in_array($viteProbe, $viteChecked, true)) { continue; }
        $viteChecked[] = $viteProbe;
        if (is_file($viteProbe)) {
            $viteManifest = json_decode((string) file_get_contents($viteProbe), true);
            break;
        }
    }
    $viteCssFile = $viteManifest['resources/css/app.css']['file'] ?? null;
    $viteJsFile = $viteManifest['resources/js/app.js']['file'] ?? null;
@endphp
@if ($viteCssFile)
<link rel="stylesheet" href="{{ asset('build/' . $viteCssFile) }}">
@endif
@if ($viteJsFile)
<script type="module" src="{{ asset('build/' . $viteJsFile) }}"></script>
@endif