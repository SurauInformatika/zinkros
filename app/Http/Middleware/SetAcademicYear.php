<?php

namespace App\Http\Middleware;

use App\Models\AcademicYear;
use App\Services\AcademicYearContext;
use App\Services\SchoolContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetAcademicYear
{
    public function __construct(
        protected AcademicYearContext $academicYearContext,
        protected SchoolContext $schoolContext
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $schoolId = $this->schoolContext->id();

        if (!$schoolId) {
            return $next($request);
        }

        $sessionKey = 'active_academic_year_id';
        $activeYearId = $request->session()->get($sessionKey);

        $academicYear = null;

        if ($activeYearId) {
            $academicYear = AcademicYear::where('id', $activeYearId)
                ->where('school_id', $schoolId)
                ->first();
        }

        if (!$academicYear) {
            $academicYear = AcademicYear::where('school_id', $schoolId)
                ->active()
                ->first();

            if ($academicYear) {
                $request->session()->put($sessionKey, $academicYear->id);
            }
        }

        if ($academicYear) {
            $this->academicYearContext->set($academicYear);
        }

        return $next($request);
    }
}
