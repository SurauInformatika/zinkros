<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\PlanMenu;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlanAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->school_id || $user->isSuperAdmin()) {
            return $next($request);
        }

        $name = $request->route()?->getName();

        if ($name === null) {
            return $next($request);
        }

        $feature = PlanMenu::featureForRoute($name);

        if ($feature !== null && ! $user->school?->planHas($feature)) {
            abort(403, 'Fitur ini tidak tersedia pada paket Anda. Silakan hubungi pengelola platform untuk upgrade paket.');
        }

        return $next($request);
    }
}