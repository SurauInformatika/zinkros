<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\SchoolContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetSchoolContext
{
    public function __construct(protected SchoolContext $context)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User) {
            $user->loadMissing('school');

            if ($user->school_id) {
                $this->context->set($user->school_id);
            } else {
                $this->context->set($request->session()->get('active_school_id'));
            }
        }

        return $next($request);
    }
}
