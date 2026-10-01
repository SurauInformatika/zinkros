<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AcademicCalendar;
use Illuminate\View\View;

class KaldikController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $calendars = AcademicCalendar::where('school_id', $user->school_id)
            ->where('status', AcademicCalendar::STATUS_FINAL)
            ->with('academicYear', 'creator:id,name')
            ->orderByDesc('created_at')
            ->get();

        return view('guru.kaldik.index', compact('calendars'));
    }

    public function show(AcademicCalendar $kaldik): View
    {
        $user = auth()->user();
        if ($kaldik->school_id !== $user->school_id) abort(403);

        $kaldik->load([
            'school',
            'academicYear',
            'template',
            'creator',
            'approver',
            'levelStructures',
            'holidays',
        ]);

        return view('guru.kaldik.show', compact('kaldik'));
    }
}
