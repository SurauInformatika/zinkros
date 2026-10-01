<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Rapor')</title>
    @include('rapor.print-css')
</head>
<body class="rapor-paper-body">
    <div class="print-toolbar no-print">
        <a href="{{ route('guru.rapor.index', ['class_id' => $student->class_id ?? null]) }}">&larr; Daftar Siswa</a>
        <div class="print-toolbar" style="margin:0; padding:0; flex:1; justify-content:flex-end;">
            <a class="select" href="{{ route('guru.rapor.student', [$student->id, 'semester' => $semester === 1 ? 2 : 1]) }}">Semester {{ $semester === 1 ? '2 (Genap)' : '1 (Ganjil)' }} &rarr;</a>
            <button type="button" class="primary" onclick="window.print()">Cetak Rapor</button>
        </div>
    </div>

    <div class="paper">
        @yield('content')
    </div>
</body>
</html>