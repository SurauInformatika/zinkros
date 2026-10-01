@php
    $viteManifest = null;
    $viteBaseDir = null;
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
            $viteBaseDir = dirname($viteProbe);
            break;
        }
    }

    $viteEchoFile = function (string $entryKey, string $wrapTag) use ($viteManifest, $viteBaseDir): void {
        if (!$viteManifest || !$viteBaseDir) { return; }
        $entry = $viteManifest[$entryKey] ?? null;
        if (! is_array($entry)) { return; }
        $file = $entry['file'] ?? null;
        $full = $file ? $viteBaseDir . DIRECTORY_SEPARATOR . $file : null;
        if (! $file || ! $full || ! is_file($full)) { return; }
        $content = (string) file_get_contents($full);
        if ($wrapTag === 'style') {
            $base = dirname($file);
            $content = preg_replace(
                '#url\((?!\s*["\']?(?:https?:|data:|/))["\']?([^"\')]+)["\']?\)#i',
                'url(/build/' . $base . '/$1)',
                $content
            ) ?: $content;
        }
        echo "<$wrapTag>" . $content . "</$wrapTag>";
    };

    $viteEchoFile('resources/css/app.css', 'style');
    if ($viteManifest && $viteBaseDir) {
        foreach (array_keys($viteManifest) as $viteKey) {
            if (str_starts_with((string) $viteKey, '_fonts-')) {
                $viteEchoFile((string) $viteKey, 'style');
            }
        }
    }
    $viteEchoFile('resources/js/app.js', 'script');
@endphp