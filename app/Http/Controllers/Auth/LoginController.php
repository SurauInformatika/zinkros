<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * Email of the school admin whose institution has a dedicated login page
     * at /auth/imb (branded with that school's logo, name and colors).
     */
    protected const IMB_EMAIL = 'imb@mail.com';

    public function showForm(): View
    {
        return view('auth.login');
    }

    public function showImbForm(): View|RedirectResponse
    {
        $school = self::imbSchool();

        if (! $school) {
            return redirect()->route('auth.login');
        }

        return view('auth.login-imb', [
            'school' => $school,
        ]);
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

    public function imbLogin(Request $request): RedirectResponse
    {
        $school = self::imbSchool();

        if (! $school) {
            return redirect()->route('auth.login');
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Kredensial yang dimasukkan tidak sesuai.']);
        }

        $user = Auth::user();

        if (! $user || $user->school_id !== $school->id) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Kredensial yang dimasukkan tidak sesuai.']);
        }

        $request->session()->regenerate();
        $request->session()->forget('url.intended');

        if ($user->mustChangePassword()) {
            return redirect()->route('ortu.password.change');
        }

        return redirect()->route(self::homeRoute($user->role));
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $imbSchoolId = User::query()->where('email', self::IMB_EMAIL)->value('school_id');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $target = ($user && $imbSchoolId && $user->school_id === $imbSchoolId)
            ? 'auth.imb'
            : 'auth.login';

        return redirect()->route($target);
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

    /**
     * The school belonging to the dedicated login account, if any.
     */
    private static function imbSchool(): ?School
    {
        $user = User::query()
            ->where('email', self::IMB_EMAIL)
            ->first();

        return $user?->school;
    }
}
