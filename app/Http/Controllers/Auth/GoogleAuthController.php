<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    private const PENDING_KEY = 'google.pending';

    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $socialUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('auth.login')
                ->with('error', 'Login Google gagal. Silakan coba lagi.');
        }

        $googleId = (string) $socialUser->getId();
        $email = strtolower((string) $socialUser->getEmail());

        $linked = User::where('google_id', $googleId)->first();
        if ($linked) {
            return $this->logUserIn($request, $linked);
        }

        $userByEmail = $email !== '' ? User::where('email', $email)->first() : null;
        if ($userByEmail) {
            if ($userByEmail->google_id && $userByEmail->google_id !== $googleId) {
                return redirect()->route('auth.login')
                    ->with('error', 'Akun ini sudah terhubung dengan akun Google lain. Silakan masuk menggunakan cara lain.');
            }

            $userByEmail->forceFill([
                'google_id' => $googleId,
                'google_avatar' => $socialUser->getAvatar(),
            ])->save();

            return $this->logUserIn($request, $userByEmail);
        }

        return redirect()->route('auth.login')
            ->with('error', 'Akun belum terdaftar di aplikasi. Silakan hubungi pihak sekolah untuk mendaftarkan akun Anda.');
    }

    public function linkForm(Request $request): View|RedirectResponse
    {
        $pending = $request->session()->get(self::PENDING_KEY);

        if (! $pending || ! User::where('email', $pending['email'])->exists()) {
            return redirect()->route('auth.login')
                ->with('error', 'Sesi konfirmasi akun Google telah berakhir. Silakan coba login dengan Google lagi.');
        }

        return view('auth.google-link', ['pending' => $pending]);
    }

    public function link(Request $request): RedirectResponse
    {
        $pending = $request->session()->get(self::PENDING_KEY);

        if (! $pending) {
            return redirect()->route('auth.login')
                ->with('error', 'Sesi konfirmasi akun Google telah berakhir. Silakan coba login dengan Google lagi.');
        }

        $data = $request->validate([
            'email' => ['required', 'email', 'in:'.$pending['email']],
            'password' => ['required'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return back()->withErrors(['password' => 'Password tidak sesuai untuk akun ini.']);
        }

        if ($user->google_id && $user->google_id !== $pending['google_id']) {
            $request->session()->forget(self::PENDING_KEY);

            return redirect()->route('auth.login')
                ->with('error', 'Akun ini sudah terhubung dengan akun Google lain. Silakan masuk menggunakan cara lain.');
        }

        $user->forceFill([
            'google_id' => $pending['google_id'],
            'google_avatar' => $pending['avatar'] ?? null,
        ])->save();

        $request->session()->forget(self::PENDING_KEY);

        return $this->logUserIn($request, $user);
    }

    public function cancel(Request $request): RedirectResponse
    {
        $request->session()->forget(self::PENDING_KEY);

        return redirect()->route('auth.login');
    }

    private function logUserIn(Request $request, User $user): RedirectResponse
    {
        Auth::login($user);

        $request->session()->regenerate();

        if ($user->mustChangePassword()) {
            return redirect()->route('ortu.password.change');
        }

        return redirect()->route(LoginController::homeRoute($user->role));
    }
}
