<?php

namespace App\Http\Controllers\Wakakur;

use App\Http\Controllers\Controller;
use App\Models\AcademicCalendar;
use App\Models\AcademicYear;
use App\Models\CalendarClassOverride;
use App\Models\CalendarHoliday;
use App\Models\CalendarLevelStructure;
use App\Models\KaldikTemplate;
use App\Models\RecurringHoliday;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class KaldikController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $calendars = AcademicCalendar::where('school_id', $user->school_id)
            ->with('academicYear', 'template')
            ->orderByDesc('created_at')
            ->get();

        return view('wakakur.kaldik.index', compact('calendars'));
    }

    public function create(): View
    {
        $user = auth()->user();
        $activeYear = AcademicYear::where('school_id', $user->school_id)->where('is_active', true)->first();
        $templates = KaldikTemplate::where('is_active', true)->orderBy('name')->get();

        return view('wakakur.kaldik.create', compact('activeYear', 'templates'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'academic_year_id' => 'required|uuid',
            'template_id'      => 'nullable|uuid',
            'name'             => 'required|string|max:255',
            'source'           => 'required|in:dindik,kemenag,custom',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after:start_date',
            'semester_2_start_date' => 'nullable|date|after:start_date|before_or_equal:end_date',
        ]);

        $validated['school_id']  = $user->school_id;
        $validated['semester']   = AcademicCalendar::SEMESTER_ANNUAL;
        $validated['status']     = 'draft';
        $validated['version']    = $this->nextVersionFor($user->school_id, $validated['academic_year_id']);
        $validated['created_by'] = $user->id;

        $calendar = AcademicCalendar::create($validated);

        if ($calendar->template_id) {
            $this->seedFromTemplate($calendar);
        }

        return redirect()->route('wakasek.kaldik.edit', $calendar)
            ->with('success', 'Kalender pendidikan berhasil dibuat.');
    }

    public function edit(AcademicCalendar $kaldik): View
    {
        $user = auth()->user();
        if ($kaldik->school_id !== $user->school_id) abort(403);

        $kaldik->load(['levelStructures', 'holidays', 'classOverrides']);

        $recurringHolidays = RecurringHoliday::where('is_active', true)->orderBy('month')->get();

        $weekDays = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'ahad'];

        return view('wakakur.kaldik.edit', compact('kaldik', 'recurringHolidays', 'weekDays'));
    }

    public function update(Request $request, AcademicCalendar $kaldik): RedirectResponse
    {
        $user = auth()->user();
        if ($kaldik->school_id !== $user->school_id) abort(403);
        if (!$kaldik->isDraft()) return back()->with('error', 'Hanya kalender DRAFT yang bisa diedit.');

        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'source'     => 'required|in:dindik,kemenag,custom',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
            'semester_2_start_date' => 'nullable|date|after:start_date|before_or_equal:end_date',
        ]);

        $validated['semester'] = AcademicCalendar::SEMESTER_ANNUAL;

        $kaldik->update($validated);

        return back()->with('success', 'Kalender pendidikan berhasil diperbarui.');
    }

    public function destroy(AcademicCalendar $kaldik): RedirectResponse
    {
        $user = auth()->user();
        if ($kaldik->school_id !== $user->school_id) abort(403);
        if (!$kaldik->isDraft()) return back()->with('error', 'Hanya kalender DRAFT yang bisa dihapus.');

        $kaldik->delete();

        return redirect()->route('wakasek.kaldik.index')
            ->with('success', 'Kalender pendidikan berhasil dihapus.');
    }

    public function submit(AcademicCalendar $kaldik): RedirectResponse
    {
        $user = auth()->user();
        if ($kaldik->school_id !== $user->school_id) abort(403);
        if (!$kaldik->isDraft()) return back()->with('error', 'Hanya kalender DRAFT yang bisa disubmit.');

        $hasStructures = $kaldik->levelStructures()->count() > 0;
        if (!$hasStructures) {
            return back()->with('error', 'Harap isi struktur JP per jenjang terlebih dahulu.');
        }

        $kaldik->update(['status' => AcademicCalendar::STATUS_PENDING]);

        // Notify Kepsek
        $kepsek = \App\Models\User::where('school_id', $user->school_id)
            ->where('role', 'kepsek')
            ->first();

        if ($kepsek) {
            \App\Models\Notification::create([
                'school_id' => $user->school_id,
                'user_id'   => $kepsek->id,
                'type'      => \App\Models\Notification::TYPE_KALDIK_APPROVAL,
                'data'      => [
                    'calendar_id' => $kaldik->id,
                    'calendar_name' => $kaldik->name,
                    'submitted_by' => $user->name,
                ],
            ]);
        }

        return back()->with('success', 'Kalender berhasil disubmit untuk approval Kepsek.');
    }

    public function levelStructure(Request $request, AcademicCalendar $kaldik): RedirectResponse
    {
        $user = auth()->user();
        if ($kaldik->school_id !== $user->school_id) abort(403);
        if (!$kaldik->isDraft()) return back()->with('error', 'Hanya kalender DRAFT yang bisa diedit.');

        $validated = $request->validate([
            'structures'   => 'required|array',
            'structures.*.grade_level_start' => 'required|integer|min:1|max:12',
            'structures.*.grade_level_end'   => 'required|integer|min:1|max:12|gte:structures.*.grade_level_start',
            'structures.*.jp_duration_minutes' => 'required|integer|min:10|max:120',
            'structures.*.jp_per_day' => 'required|array',
            'structures.*.jp_per_day.*' => 'required|integer|min:0|max:20',
        ]);

        // Clear existing and recreate
        $kaldik->levelStructures()->delete();

        $days = CalendarLevelStructure::DAYS;
        foreach ($validated['structures'] as $struct) {
            $map = array_intersect_key($struct['jp_per_day'], array_flip($days));
            foreach ($days as $day) {
                $map[$day] = (int) ($map[$day] ?? 0);
            }

            CalendarLevelStructure::create([
                'academic_calendar_id' => $kaldik->id,
                'grade_level_start'    => $struct['grade_level_start'],
                'grade_level_end'      => $struct['grade_level_end'],
                'jp_duration_minutes'  => $struct['jp_duration_minutes'],
                'jp_per_day'           => $map,
            ]);
        }

        $kaldik->load('levelStructures');
        $warnings = $kaldik->levelStructureWarnings();
        if ($warnings) {
            return back()->with('success', 'Struktur & durasi JP per jenjang berhasil disimpan.')->with('warning', implode(' ', $warnings));
        }

        return back()->with('success', 'Struktur & durasi JP per jenjang berhasil disimpan.');
    }

    public function storeHoliday(Request $request, AcademicCalendar $kaldik): RedirectResponse
    {
        $user = auth()->user();
        if ($kaldik->school_id !== $user->school_id) abort(403);
        if (!$kaldik->isDraft()) return back()->with('error', 'Hanya kalender DRAFT yang bisa diedit.');

        $validated = $request->validate([
            'date' => 'required|date',
            'name' => 'required|string|max:255',
            'type' => 'required|in:nasional,daerah,sekolah,rutin',
        ]);

        CalendarHoliday::create([
            'academic_calendar_id' => $kaldik->id,
            'date' => $validated['date'],
            'name' => $validated['name'],
            'type' => $validated['type'],
        ]);

        return back()->with('success', 'Libur berhasil ditambahkan.');
    }

    public function destroyHoliday(AcademicCalendar $kaldik, CalendarHoliday $holiday): RedirectResponse
    {
        $user = auth()->user();
        if ($kaldik->school_id !== $user->school_id) abort(403);
        if (!$kaldik->isDraft()) return back()->with('error', 'Hanya kalender DRAFT yang bisa diedit.');

        $holiday->delete();

        return back()->with('success', 'Libur berhasil dihapus.');
    }

    public function storeOverride(Request $request, AcademicCalendar $kaldik): RedirectResponse
    {
        $user = auth()->user();
        if ($kaldik->school_id !== $user->school_id) abort(403);
        if (!$kaldik->isDraft()) return back()->with('error', 'Hanya kalender DRAFT yang bisa diedit.');

        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'grade_level'   => 'nullable|integer|min:1|max:12',
            'notes'         => 'nullable|string|max:1000',
        ]);

        CalendarClassOverride::create([
            'academic_calendar_id' => $kaldik->id,
            'override_type' => 'other',
            'title'         => $validated['title'],
            'start_date'    => $validated['start_date'],
            'end_date'      => $validated['end_date'],
            'grade_level'   => $validated['grade_level'] ?? null,
            'notes'         => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Kegiatan khusus berhasil ditambahkan.');
    }

    public function destroyOverride(AcademicCalendar $kaldik, CalendarClassOverride $override): RedirectResponse
    {
        $user = auth()->user();
        if ($kaldik->school_id !== $user->school_id) abort(403);
        if (!$kaldik->isDraft()) return back()->with('error', 'Hanya kalender DRAFT yang bisa diedit.');

        $override->delete();

        return back()->with('success', 'Override kelas berhasil dihapus.');
    }

    public function storeDetails(Request $request, AcademicCalendar $kaldik): RedirectResponse
    {
        $user = auth()->user();
        if ($kaldik->school_id !== $user->school_id) abort(403);
        if (!$kaldik->isDraft()) return back()->with('error', 'Hanya kalender DRAFT yang bisa diedit.');

        $validated = $request->validate([
            'holidays'            => 'nullable|array',
            'holidays.*.date'     => 'required|date',
            'holidays.*.name'     => 'required|string|max:255',
            'holidays.*.type'     => 'required|in:nasional,daerah,sekolah,rutin',
            'overrides'           => 'nullable|array',
            'overrides.*.title'   => 'required|string|max:255',
            'overrides.*.start_date' => 'required|date',
            'overrides.*.end_date'   => 'required|date|after_or_equal:overrides.*.start_date',
            'overrides.*.grade_level' => 'nullable|integer|min:1|max:12',
            'overrides.*.notes'   => 'nullable|string|max:1000',
        ]);

        $count = 0;
        DB::transaction(function () use ($validated, $kaldik, &$count) {
            foreach ($validated['holidays'] ?? [] as $h) {
                CalendarHoliday::create([
                    'academic_calendar_id' => $kaldik->id,
                    'date' => $h['date'],
                    'name' => $h['name'],
                    'type' => $h['type'],
                ]);
                $count++;
            }
            foreach ($validated['overrides'] ?? [] as $o) {
                CalendarClassOverride::create([
                    'academic_calendar_id' => $kaldik->id,
                    'override_type' => 'other',
                    'title'         => $o['title'],
                    'start_date'    => $o['start_date'],
                    'end_date'      => $o['end_date'],
                    'grade_level'   => $o['grade_level'] ?? null,
                    'notes'         => $o['notes'] ?? null,
                ]);
                $count++;
            }
        });

        return back()->with('success', $count > 0 ? 'Rincian kalender berhasil disimpan (' . $count . ' item).' : 'Tidak ada item untuk disimpan.');
    }

    public function copy(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'source_calendar_id' => 'required|uuid',
            'name'               => 'required|string|max:255',
            'academic_year_id'   => 'required|uuid',
            'start_date'         => 'required|date',
            'end_date'           => 'required|date|after:start_date',
            'semester_2_start_date' => 'nullable|date|after:start_date|before_or_equal:end_date',
        ]);

        $source = AcademicCalendar::where('school_id', $user->school_id)
            ->where('id', $validated['source_calendar_id'])
            ->firstOrFail();

        DB::beginTransaction();

        $newCalendar = AcademicCalendar::create([
            'school_id'       => $user->school_id,
            'academic_year_id' => $validated['academic_year_id'],
            'template_id'     => $source->template_id,
            'name'            => $validated['name'],
            'semester'        => AcademicCalendar::SEMESTER_ANNUAL,
            'source'          => $source->source,
            'start_date'      => $validated['start_date'],
            'end_date'        => $validated['end_date'],
            'semester_2_start_date' => $validated['semester_2_start_date'] ?? null,
            'status'          => 'draft',
            'version'         => $this->nextVersionFor($user->school_id, $validated['academic_year_id']),
            'created_by'      => $user->id,
        ]);

        $this->duplicateData($source, $newCalendar);

        DB::commit();

        return redirect()->route('wakasek.kaldik.edit', $newCalendar)
            ->with('success', 'Kalender berhasil disalin dari "' . $source->name . '".');
    }

    public function revise(AcademicCalendar $kaldik): RedirectResponse
    {
        $user = auth()->user();
        if ($kaldik->school_id !== $user->school_id) abort(403);
        if (!$kaldik->isFinal()) return back()->with('error', 'Hanya kalender FINAL yang bisa direvisi.');

        if ($kaldik->versions()->whereIn('status', ['draft', 'pending'])->exists()) {
            return back()->with('error', 'Masih ada revisi yang belum selesai disetujui. Selesaikan atau hapus revisi tersebut terlebih dahulu.');
        }

        $nextVersion = $this->nextVersionFor($kaldik->school_id, $kaldik->academic_year_id);
        if ($nextVersion < 2) {
            $nextVersion = 2;
        }

        DB::beginTransaction();

        $revision = AcademicCalendar::create([
            'school_id'         => $user->school_id,
            'academic_year_id'  => $kaldik->academic_year_id,
            'template_id'       => $kaldik->template_id,
            'name'              => $kaldik->name,
            'semester'          => $kaldik->semester,
            'source'            => $kaldik->source,
            'start_date'        => $kaldik->start_date,
            'end_date'          => $kaldik->end_date,
            'semester_2_start_date' => $kaldik->semester_2_start_date,
            'status'            => AcademicCalendar::STATUS_DRAFT,
            'version'           => $nextVersion,
            'parent_version_id' => $kaldik->id,
            'created_by'        => $user->id,
        ]);

        $this->duplicateData($kaldik, $revision);

        DB::commit();

        return redirect()->route('wakasek.kaldik.edit', $revision)
            ->with('success', 'Revisi v' . $nextVersion . ' dibuat dari kalender FINAL. Silakan edit lalu submit kembali untuk approval.');
    }

    private function nextVersionFor(string $schoolId, string $academicYearId): int
    {
        return (int) AcademicCalendar::where('school_id', $schoolId)
            ->where('academic_year_id', $academicYearId)
            ->max('version') + 1;
    }

    private function duplicateData(AcademicCalendar $source, AcademicCalendar $target): void
    {
        foreach ($source->levelStructures as $ls) {
            CalendarLevelStructure::create([
                'academic_calendar_id' => $target->id,
                'grade_level_start'    => $ls->grade_level_start,
                'grade_level_end'      => $ls->grade_level_end,
                'jp_duration_minutes'  => $ls->jp_duration_minutes,
                'jp_per_day'           => $ls->jp_per_day,
            ]);
        }

        foreach ($source->holidays as $h) {
            CalendarHoliday::create([
                'academic_calendar_id' => $target->id,
                'date' => $h->date,
                'name' => $h->name,
                'type' => $h->type,
            ]);
        }

        foreach ($source->classOverrides as $co) {
            CalendarClassOverride::create([
                'academic_calendar_id' => $target->id,
                'override_type'        => $co->override_type,
                'title'                => $co->title,
                'start_date'           => $co->start_date,
                'end_date'             => $co->end_date,
                'grade_level'          => $co->grade_level,
                'notes'                => $co->notes,
            ]);
        }
    }

    public function seedFromTemplate(AcademicCalendar $calendar): void
    {
        $template = $calendar->template;
        if (!$template) return;

        $dayMap = $template->default_day_structure ?? [];
        $days = CalendarLevelStructure::DAYS;
        $normalized = [];
        foreach ($days as $day) {
            $normalized[$day] = (int) ($dayMap[$day] ?? 0);
        }

        $levels = $template->default_level_structure;
        if (empty($levels)) {
            $levels = [[
                'start' => 1,
                'end' => $calendar->maxGradeLevel(),
                'menit' => 35,
            ]];
        }

        foreach ($levels as $ls) {
            CalendarLevelStructure::create([
                'academic_calendar_id' => $calendar->id,
                'grade_level_start'    => $ls['start'] ?? 1,
                'grade_level_end'      => $ls['end'] ?? 6,
                'jp_duration_minutes'  => $ls['menit'] ?? 35,
                'jp_per_day'           => $normalized,
            ]);
        }
    }
}
