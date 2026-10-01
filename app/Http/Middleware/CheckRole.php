<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('auth.login');
        }

        $user = $request->user();

        if (! in_array($user->role, $roles, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        if ($user->school_id && $user->school?->isSuspended()) {
            $reason = $user->school->suspended_reason;
            $message = 'Akses ditangguhkan.';
            if ($reason) {
                $message .= ' Alasan: ' . $reason;
            }

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('auth.login')
                ->with('error', $message);
        }

        if ($user->school_id && $user->school?->isExpired()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('auth.login')
                ->with('error', 'Masa berlangganan telah berakhir. Silakan hubungi pengelola platform.');
        }

        if ($user->school_id && $user->school?->isTrialExpired()) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('auth.login')
                ->with('error', 'Masa trial telah berakhir. Silakan hubungi pengelola platform.');
        }

        return $next($request);
    }
}
