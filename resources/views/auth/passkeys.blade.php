@extends('layouts.app')

@section('title', 'Keamanan Akun - ' . \App\Models\PlatformSetting::appName())

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center gap-4 mb-6">
        <a href="javascript:history.back()" class="p-2 -ml-2 rounded-lg text-slate-500 dark:text-white/50 hover:bg-slate-100 dark:hover:bg-white/10 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-xl font-bold">Keamanan Akun</h1>
            <p class="text-sm text-slate-500 dark:text-white/40">Kelola passkey (sidik jari, wajah, atau PIN perangkat) untuk login tanpa password.</p>
        </div>
    </div>

    <div class="bg-white dark:bg-[#141414] rounded-2xl shadow-sm border border-slate-200 dark:border-white/10">
        <div class="p-6 border-b border-slate-200 dark:border-white/10 flex items-center justify-between gap-4 flex-wrap">
            <div>
                <h2 class="text-sm font-semibold mb-1">Passkey Saya</h2>
                <p class="text-xs text-slate-500 dark:text-white/40">Gunakan passkey sebagai cara masuk lain ke aplikasi ini.</p>
            </div>
            <button type="button" id="addPasskeyBtn"
                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-primary to-secondary hover:from-primary-dark hover:to-secondary-dark px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-primary/25 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Tambah Passkey
            </button>
        </div>

        <div class="p-6">
            <label for="passkeyName" class="block text-sm font-medium text-slate-700 dark:text-white/70 mb-1">Nama Passkey</label>
            <input id="passkeyName" type="text" maxlength="100" value="Passkey {{ auth()->user()->name }}"
                class="w-full rounded-xl border border-slate-300 dark:border-white/10 bg-white dark:bg-[#0a0a0a] dark:text-white px-4 py-2.5 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition placeholder:text-slate-400 dark:placeholder:text-white/30">
            <p class="mt-1 text-xs text-slate-500 dark:text-white/40">Misalnya nama perangkat: "Windows Laptop", "HP Android".</p>

            <p id="passkeyError" class="hidden mt-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 px-4 py-3 text-sm text-red-700 dark:text-red-400"></p>
            <p id="passkeySuccess" class="hidden mt-4 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-400"></p>

            @if ($keys->isEmpty())
                <div class="mt-6 rounded-xl border border-dashed border-slate-300 dark:border-white/10 px-6 py-10 text-center">
                    <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-white/20 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 113 0m0-6a1.5 1.5 0 113 0m0 0V14m0 0v1a3 3 0 11-6 0v-1m6 0H10m5.25-6.5a1.5 1.5 0 113 0m0 0a1.5 1.5 0 113 0m-3 0v1m6 0a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 11-3 0m-3 0v1m-6 1.5h.01m12 4.5h.01"/></svg>
                    <p class="text-sm font-medium text-slate-600 dark:text-white/60">Belum ada passkey terdaftar</p>
                    <p class="text-xs text-slate-400 dark:text-white/30 mt-1">Klik "Tambah Passkey" untuk mendaftarkan perangkat ini.</p>
                </div>
            @else
                <ul class="mt-6 space-y-3">
                    @foreach ($keys as $key)
                        <li class="flex items-center justify-between gap-4 rounded-xl border border-slate-200 dark:border-white/10 px-4 py-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-9 h-9 rounded-lg bg-primary/10 border border-primary/20 flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-primary" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium truncate">{{ $key->name }}</p>
                                    <p class="text-xs text-slate-400 dark:text-white/30">Didaftarkan {{ $key->created_at->translatedFormat('d M Y') }}</p>
                                </div>
                            </div>
                            <form method="POST" action="{{ route('webauthn.destroy', $key->id) }}" onsubmit="return confirm('Hapus passkey ini? Anda tidak bisa login dengan passkey tersebut lagi.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Hapus
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="mt-6 rounded-xl border border-slate-200 dark:border-white/10 bg-white dark:bg-[#141414] px-6 py-5">
        <h2 class="text-sm font-semibold mb-2">Cara login dengan passkey</h2>
        <ol class="list-decimal list-inside text-sm text-slate-600 dark:text-white/50 space-y-1">
            <li>Buka halaman masuk, isi email Anda.</li>
            <li>Klik "Masuk dengan Passkey", lalu konfirmasi dengan sidik jari, wajah, atau PIN perangkat Anda.</li>
        </ol>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('vendor/webauthn/webauthn.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('addPasskeyBtn');
    if (!btn) return;

    var errorBox = document.getElementById('passkeyError');
    var successBox = document.getElementById('passkeySuccess');
    var nameInput = document.getElementById('passkeyName');

    function showError(message) {
        successBox.classList.add('hidden');
        errorBox.textContent = message;
        errorBox.classList.remove('hidden');
    }

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]').content;
    }

    btn.addEventListener('click', function () {
        errorBox.classList.add('hidden');
        successBox.classList.add('hidden');

        if (!window.PublicKeyCredential) {
            showError('Browser ini tidak mendukung passkey (WebAuthn).');
            return;
        }

        btn.disabled = true;

        fetch("{{ route('webauthn.store.options') }}", {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
        })
        .then(function (response) { return response.json(); })
        .then(function (data) {
            var webauthn = new WebAuthn(function (name, message) {
                showError(name === 'NotAllowedError' ? 'Autentikasi dibatalkan.' : message);
            });

            webauthn.register(data.publicKey, function (credential) {
                var body = Object.assign({}, credential, {
                    name: nameInput.value.trim() || ('Passkey ' + nameInput.defaultValue),
                });

                fetch("{{ route('webauthn.store') }}", {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                    body: JSON.stringify(body),
                })
                .then(function (response) {
                    if (response.ok) {
                        window.location.reload();
                        return;
                    }
                    return response.json().then(function (res) {
                        var first = Object.values(res.errors || {})[0];
                        throw new Error(first ? first[0] : 'Gagal mendaftarkan passkey.');
                    });
                })
                .catch(function (error) {
                    showError(error.message);
                    btn.disabled = false;
                });
            });
        })
        .catch(function () {
            showError('Gagal meminta data passkey dari server. Coba lagi.');
            btn.disabled = false;
        });
    });
});
</script>
@endpush