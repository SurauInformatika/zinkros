<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\KaldikTemplate;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KaldikTemplateController extends Controller
{
    public function index(): View
    {
        $templates = KaldikTemplate::withCount('academicCalendars')
            ->orderByDesc('created_at')
            ->get();

        return view('platform.kaldik.templates.index', compact('templates'));
    }

    public function create(): View
    {
        return view('platform.kaldik.templates.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'source' => 'required|in:dindik,kemenag,custom',
            'default_day_structure' => 'nullable|array',
            'default_level_structure' => 'nullable|array',
            'default_holidays' => 'nullable|array',
        ]);

        KaldikTemplate::create($validated);

        return redirect()->route('platform.kaldik-templates.index')
            ->with('status', 'Template berhasil dibuat.');
    }

    public function edit(KaldikTemplate $kaldikTemplate): View
    {
        return view('platform.kaldik.templates.edit', ['template' => $kaldikTemplate]);
    }

    public function update(Request $request, KaldikTemplate $kaldikTemplate): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'source' => 'required|in:dindik,kemenag,custom',
            'default_day_structure' => 'nullable|array',
            'default_level_structure' => 'nullable|array',
            'default_holidays' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $kaldikTemplate->update($validated);

        return redirect()->route('platform.kaldik-templates.index')
            ->with('status', 'Template berhasil diperbarui.');
    }

    public function destroy(KaldikTemplate $kaldikTemplate): RedirectResponse
    {
        if ($kaldikTemplate->academicCalendars()->exists()) {
            return back()->with('error', 'Template tidak bisa dihapus karena sudah dipakai oleh sekolah.');
        }

        $kaldikTemplate->delete();

        return redirect()->route('platform.kaldik-templates.index')
            ->with('status', 'Template berhasil dihapus.');
    }

    public function pushToSchools(Request $request, KaldikTemplate $kaldikTemplate): RedirectResponse
    {
        $schools = School::where('status', 'active')->get();
        $pushed = 0;

        foreach ($schools as $school) {
            $activeYear = $school->academicYears()->where('is_active', true)->first();

            if (!$activeYear) {
                continue;
            }

            $hasExisting = $school->academicCalendars()
                ->where('template_id', $kaldikTemplate->id)
                ->where('academic_year_id', $activeYear->id)
                ->exists();

            if (!$hasExisting) {
                $school->academicCalendars()->create([
                    'academic_year_id' => $activeYear->id,
                    'template_id' => $kaldikTemplate->id,
                    'name' => 'KALDIK dari Template: ' . $kaldikTemplate->name,
                    'semester' => 1,
                    'source' => $kaldikTemplate->source,
                    'start_date' => $activeYear->start_date,
                    'end_date' => $activeYear->end_date,
                    'status' => 'draft',
                    'created_by' => auth()->id(),
                ]);
                $pushed++;
            }
        }

        return redirect()->route('platform.kaldik-templates.index')
            ->with('status', "Template berhasil di-push ke {$pushed} sekolah.");
    }
}
