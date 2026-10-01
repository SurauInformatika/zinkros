<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Kredensial yang dimasukkan tidak sesuai.']);
        }

        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        $user = Auth::user();
        if ($user->mustChangePassword()) {
            return redirect()->route('ortu.password.change');
        }

        return redirect()->route(self::homeRoute(Auth::user()->role));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.login');
    }

    public static function homeRoute(string $role): string
    {
        return match ($role) {
            'superadmin' => 'platform.dashboard',
            'admin' => 'admin.dashboard',
            'guru' => 'guru.dashboard',
            'wakakur' => 'wakasek.dashboard',
            'kepsek' => 'kepsek.dashboard',
            'wakamur' => 'wakasek.dashboard',
            'wakasek' => 'wakasek.dashboard',
            'murid' => 'murid.dashboard',
            'keuangan' => 'keuangan.dashboard',
            'staff' => 'staff.dashboard',
            default => 'ortu.dashboard',
        };
    }
}
