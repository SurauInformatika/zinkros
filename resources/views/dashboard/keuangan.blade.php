@extends('layouts.app')

@section('title', 'Dashboard Keuangan')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold">Dashboard Keuangan</h1>
    <form method="POST" action="{{ route('auth.logout') }}">
        @csrf
        <button type="submit" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm hover:bg-slate-50">
            Keluar
        </button>
    </form>
</div>

<div class="rounded-xl bg-white border border-slate-200 p-5">
    <p class="text-sm text-slate-500">Rekap SPP</p>
    <p class="mt-2 text-sm text-slate-600">Ringkasan tagihan dan pembayaran akan tampil di sini.</p>
</div>
@endsection
