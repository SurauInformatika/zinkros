<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $schoolId = $user->school_id;

        $classSubjectTeachers = $user->classSubjectTeachers()
            ->with(['classRoom', 'subject'])
            ->get();

        $quranAssignments = $user->quranTeachingAssignments()
            ->with('student.classRoom')
            ->get();

        $waliClasses = $user->waliClasses()->get();

        $taughtSubjectsByClass = $classSubjectTeachers
            ->groupBy(fn ($cst) => $cst->classRoom?->class_name ?? '-')
            ->map(fn ($items, $className) => [
                'class_name' => $className,
                'subjects' => $items->pluck('subject.name')->unique()->values(),
            ])
            ->values();

        $totalMapelSiswa = $classSubjectTeachers->count();
        $totalTahfidzSiswa = $quranAssignments->count();

        return view('guru.profile.index', compact(
            'user',
            'classSubjectTeachers',
            'quranAssignments',
            'waliClasses',
            'taughtSubjectsByClass',
            'totalMapelSiswa',
            'totalTahfidzSiswa',
        ));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);
        $user->update($validated);
        return redirect()->route('guru.profile.index')->with('status', 'Profil berhasil diperbarui.');
    }

    public function password(): View
    {
        return view('guru.profile.password');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
        auth()->user()->update([
            'password' => Hash::make($validated['password']),
        ]);
        return redirect()->route('guru.profile.password')->with('status', 'Password berhasil diubah.');
    }
}
