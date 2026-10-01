<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    /** Routes that must never trigger the forced password change. */
    private const SKIP_ROUTES = [
        'auth.login',
        'auth.login.post',
        'auth.logout',
        'auth.forgot',
        'auth.forgot.post',
        'auth.reset',
        'auth.reset.post',
        'ortu.password.change',
        'ortu.password.change.store',
    ];

    /**
     * Force ortu accounts that were created with the default temporary
     * password to change it before accessing the rest of the app.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()
            && Auth::user()->mustChangePassword()
            && ! $request->routeIs(self::SKIP_ROUTES)) {
            return redirect()->route('ortu.password.change');
        }

        return $next($request);
    }
}
