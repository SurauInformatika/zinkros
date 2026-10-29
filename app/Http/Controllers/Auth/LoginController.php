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
     * Branded login pages keyed by URL segment. Each value is the admin account
     * that anchors the school, so the page is branded with that school's logo,
     * name and colors (e.g. /auth/imb, /auth/smpdafi).
     */
    protected const BRANDED_LOGINS = [
        'imb' => 'imb@mail.com',
        'smpdafi' => 'adhir@mail.com',
    ];

    public function showForm(): View
    {
        return view('auth.login');
    }

    public function showImbForm(): View|RedirectResponse
    {
        return $this->showBrandedForm('imb');
    }

    public function showSmpdafiForm(): View|RedirectResponse
    {
        return $this->showBrandedForm('smpdafi');
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
        return $this->brandedLogin($request, 'imb');
    }

    public function smpdafiLogin(Request $request): RedirectResponse
    {
        return $this->brandedLogin($request, 'smpdafi');
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $brand = null;

        if ($user && filled($user->school_id)) {
            $brand = self::brandedKeyForSchool((string) $user->school_id);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($brand ? 'auth.' . $brand : 'auth.login');
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
     * Render the branded login page for the given brand, or fall back to the
     * general login when the anchor school cannot be resolved.
     */
    protected function showBrandedForm(string $brand): View|RedirectResponse
    {
        $school = self::brandedSchool($brand);

        if (! $school) {
            return redirect()->route('auth.login');
        }

        return view('auth.login-branded', [
            'school' => $school,
            'brand' => $brand,
        ]);
    }

    /**
     * Authenticate against a branded login, restricted to users that belong to
     * the anchored school.
     */
    protected function brandedLogin(Request $request, string $brand): RedirectResponse
    {
        $school = self::brandedSchool($brand);

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

    /**
     * The school belonging to a branded login's anchor account, if any.
     */
    protected static function brandedSchool(string $brand): ?School
    {
        $email = self::BRANDED_LOGINS[$brand] ?? null;

        if (! $email) {
            return null;
        }

        $user = User::query()->where('email', $email)->first();

        return $user?->school;
    }

    /**
     * The branded login whose anchor account belongs to the given school.
     */
    protected static function brandedKeyForSchool(?string $schoolId): ?string
    {
        if (blank($schoolId)) {
            return null;
        }

        foreach (self::BRANDED_LOGINS as $brand => $email) {
            $anchorSchoolId = User::query()->where('email', $email)->value('school_id');

            if ($anchorSchoolId && (string) $anchorSchoolId === (string) $schoolId) {
                return $brand;
            }
        }

        return null;
    }
}
