<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\QuranMaster;
use App\Models\QuranTeachingAssignment;
use App\Models\Student;
use App\Models\TasmiRecord;
use App\Models\User;
use App\Services\AcademicYearContext;
use App\Services\QuranJuzService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TasmiController extends Controller
{
    public function __construct(
        protected AcademicYearContext $academicYearContext,
        protected QuranJuzService $quranJuzService,
    ) {}

    public function students(Request $request): View
    {
        $user = $request->user();
        $ayId = $this->academicYearContext->id();
        $studentId = old('student_id');
        $assignmentStudentIds = QuranTeachingAssignment::query()
            ->select('student_id')
            ->where('teacher_id', $user->id)
            ->when($ayId, fn ($query) => $query->where(function ($query) use ($ayId) {
                $query->where('academic_year_id', $ayId)->orWhereNull('academic_year_id');
            }));
        $selectedStudent = $studentId
            ? Student::query()
                ->where('school_id', $user->school_id)
                ->whereKey($studentId)
                ->when(! $this->canAssessAllStudents($user), fn ($query) => $query->whereIn('id', $assignmentStudentIds))
                ->with('classRoom')
                ->first()
            : null;
        $surahs = QuranMaster::orderBy('surah_number')->get();

        return view('guru.tasmi.students', [
            'selectedStudent' => $selectedStudent,
            'canAssessAllStudents' => $this->canAssessAllStudents($user),
            'date' => old('recorded_date', Carbon::today()->toDateString()),
            'surahMap' => $this->quranJuzService->allJuz($surahs),
            'pageMap' => $this->quranJuzService->allJuzPages($surahs),
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'required|string|min:3|max:100',
        ]);
        $user = $request->user();
        $ayId = $this->academicYearContext->id();

        $assignmentStudentIds = QuranTeachingAssignment::query()
            ->select('student_id')
            ->where('teacher_id', $user->id)
            ->when($ayId, fn ($query) => $query->where(function ($query) use ($ayId) {
                $query->where('academic_year_id', $ayId)->orWhereNull('academic_year_id');
            }));

        $students = Student::query()
            ->where('school_id', $user->school_id)
            ->when(! $this->canAssessAllStudents($user), fn ($query) => $query->whereIn('id', $assignmentStudentIds))
            ->where('name', 'like', '%'.$validated['q'].'%')
            ->with('classRoom')
            ->orderBy('name')
            ->limit(10)
            ->get()
            ->map(fn (Student $student) => [
                'id' => $student->id,
                'name' => $student->name,
                'class' => $student->classRoom?->class_name,
                'nis' => $student->nis,
            ])
            ->values();

        return response()->json($students);
    }

    public function rekap(Request $request): View
    {
        $records = TasmiRecord::query()
            ->where('teacher_id', $request->user()->id)
            ->with(['student.classRoom', 'items.quranMaster'])
            ->orderByDesc('recorded_date')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('guru.tasmi.rekap', compact('records'));
    }

    public function store(Request $request)
    {
        foreach (['pages', 'surahs'] as $inputKey) {
            $entries = $request->input($inputKey);
            if (is_array($entries)) {
                $entries = array_values(array_filter($entries, function ($entry) {
                    if (! is_array($entry)) {
                        return true;
                    }

                    return (($entry['score'] ?? null) !== null && ($entry['score'] ?? '') !== '')
                        || (($entry['fluency_score'] ?? null) !== null && ($entry['fluency_score'] ?? '') !== '')
                        || (($entry['tajwid_score'] ?? null) !== null && ($entry['tajwid_score'] ?? '') !== '')
                        || (($entry['fasohah_score'] ?? null) !== null && ($entry['fasohah_score'] ?? '') !== '')
                        || trim((string) ($entry['notes'] ?? '')) !== '';
                }));
                $request->merge([$inputKey => $entries]);
            }
        }

        $validated = $request->validate([
            'student_id' => 'required|uuid',
            'recorded_date' => 'nullable|date|before_or_equal:today',
            'juz' => 'nullable|integer|between:1,30',
            'pages' => 'nullable|array',
            'pages.*.page_number' => 'required|integer|between:1,604|distinct',
            'pages.*.score' => 'nullable|integer|min:0|max:100',
            'pages.*.fluency_score' => 'nullable|integer|min:0|max:100',
            'pages.*.tajwid_score' => 'nullable|integer|min:0|max:100',
            'pages.*.fasohah_score' => 'nullable|integer|min:0|max:100',
            'pages.*.notes' => 'nullable|string|max:500',
            'pages.*.segments' => 'required|array|min:1',
            'pages.*.segments.*.quran_master_id' => 'required|uuid|exists:quran_masters,id',
            'pages.*.segments.*.ayat_start' => 'required|integer|min:1',
            'pages.*.segments.*.ayat_end' => 'required|integer|min:1',
            'surahs' => 'nullable|array',
            'surahs.*.quran_master_id' => 'required|uuid|distinct|exists:quran_masters,id',
            'surahs.*.ayat_start' => 'required|integer|min:1',
            'surahs.*.ayat_end' => 'required|integer|min:1',
            'surahs.*.score' => 'nullable|integer|min:0|max:100',
            'surahs.*.fluency_score' => 'nullable|integer|min:0|max:100',
            'surahs.*.tajwid_score' => 'nullable|integer|min:0|max:100',
            'surahs.*.fasohah_score' => 'nullable|integer|min:0|max:100',
            'surahs.*.notes' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $ayId = $this->academicYearContext->id();
        $studentExists = Student::query()
            ->where('school_id', $user->school_id)
            ->whereKey($validated['student_id'])
            ->exists();

        $assignmentExists = $this->canAssessAllStudents($user) || QuranTeachingAssignment::query()
            ->where('teacher_id', $user->id)
            ->where('student_id', $validated['student_id'])
            ->when($ayId, fn ($query) => $query->where(function ($query) use ($ayId) {
                $query->where('academic_year_id', $ayId)->orWhereNull('academic_year_id');
            }))
            ->exists();

        if (! $studentExists || ! $assignmentExists) {
            return back()->withInput()->with('error', 'Anda tidak memiliki akses untuk siswa ini.');
        }

        $pages = $validated['pages'] ?? [];
        $surahEntries = $validated['surahs'] ?? [];
        if ((! empty($pages) || ! empty($surahEntries)) && empty($validated['juz'])) {
            return back()->withInput()->withErrors([
                'juz' => 'Pilih juz untuk menyimpan nilai atau catatan Tasmi\'.',
            ]);
        }

        if (! empty($pages) && (! empty($surahEntries) || $validated['juz'] > 25)) {
            return back()->withInput()->withErrors([
                'pages' => 'Juz 26–30 harus diinput per surah, sedangkan juz 1–25 per halaman.',
            ]);
        }

        if (! empty($surahEntries) && (! empty($pages) || $validated['juz'] <= 25)) {
            return back()->withInput()->withErrors([
                'surahs' => 'Juz 26–30 harus diinput per surah, sedangkan juz 1–25 per halaman.',
            ]);
        }

        $itemsToSave = [];
        if (! empty($pages)) {
            $surahs = QuranMaster::orderBy('surah_number')->get();
            $expectedPages = $this->quranJuzService->pagesForJuz((int) $validated['juz'], $surahs);
            $expectedByPage = collect($expectedPages)->keyBy('number');

            foreach ($pages as $page) {
                $expectedPage = $expectedByPage->get((int) $page['page_number']);
                if (! $expectedPage) {
                    return back()->withInput()->withErrors([
                        'pages' => 'Halaman tidak sesuai dengan juz yang dipilih.',
                    ]);
                }

                $expectedSegments = collect($expectedPage['segments'])->keyBy('id');
                $submittedSegments = collect($page['segments'])->keyBy('quran_master_id');

                if ($expectedSegments->keys()->sort()->values()->all() !== $submittedSegments->keys()->sort()->values()->all()) {
                    return back()->withInput()->withErrors([
                        'pages' => 'Daftar surah dalam halaman tidak sesuai dengan juz yang dipilih.',
                    ]);
                }

                foreach ($page['segments'] as $segment) {
                    $bounds = $expectedSegments->get($segment['quran_master_id']);
                    if (
                        (int) $segment['ayat_start'] !== $bounds['start']
                        || (int) $segment['ayat_end'] !== $bounds['end']
                    ) {
                        return back()->withInput()->withErrors([
                            'pages' => 'Rentang ayat halaman tidak sesuai dengan Mushaf Madinah.',
                        ]);
                    }

                    $itemsToSave[] = [
                        'page_number' => (int) $page['page_number'],
                        'quran_master_id' => $segment['quran_master_id'],
                        'ayat_start' => (int) $segment['ayat_start'],
                        'ayat_end' => (int) $segment['ayat_end'],
                        'score' => $page['score'] ?? null,
                        'fluency_score' => $page['fluency_score'] ?? null,
                        'tajwid_score' => $page['tajwid_score'] ?? null,
                        'fasohah_score' => $page['fasohah_score'] ?? null,
                        'notes' => $page['notes'] ?? null,
                    ];
                }
            }
        }

        if (! empty($surahEntries)) {
            $surahs = QuranMaster::orderBy('surah_number')->get();
            $expectedSurahs = collect($this->quranJuzService->surahsForJuz((int) $validated['juz'], $surahs))
                ->keyBy('id');

            foreach ($surahEntries as $entry) {
                $bounds = $expectedSurahs->get($entry['quran_master_id']);
                if (! $bounds) {
                    return back()->withInput()->withErrors([
                        'surahs' => 'Surah tidak sesuai dengan juz yang dipilih.',
                    ]);
                }

                if (
                    (int) $entry['ayat_start'] !== $bounds['start']
                    || (int) $entry['ayat_end'] !== $bounds['end']
                ) {
                    return back()->withInput()->withErrors([
                        'surahs' => 'Rentang ayat surah tidak sesuai dengan batas juz.',
                    ]);
                }

                $itemsToSave[] = [
                    'page_number' => null,
                    'quran_master_id' => $entry['quran_master_id'],
                    'ayat_start' => (int) $entry['ayat_start'],
                    'ayat_end' => (int) $entry['ayat_end'],
                    'score' => $entry['score'] ?? null,
                    'fluency_score' => $entry['fluency_score'] ?? null,
                    'tajwid_score' => $entry['tajwid_score'] ?? null,
                    'fasohah_score' => $entry['fasohah_score'] ?? null,
                    'notes' => $entry['notes'] ?? null,
                ];
            }
        }

        DB::transaction(function () use ($validated, $user, $ayId, $itemsToSave) {
            $record = TasmiRecord::create([
                'school_id' => $user->school_id,
                'academic_year_id' => $ayId,
                'student_id' => $validated['student_id'],
                'teacher_id' => $user->id,
                'juz' => $validated['juz'] ?? null,
                'recorded_date' => $validated['recorded_date'] ?? Carbon::today()->toDateString(),
            ]);

            foreach ($itemsToSave as $item) {
                $record->items()->create($item);
            }
        });

        return redirect()->route('guru.tasmi.students')->with('success', 'Data Tasmi berhasil disimpan.');
    }

    private function canAssessAllStudents(User $user): bool
    {
        return (bool) $user->is_pj_tahfidz
            || $user->isQuranTeacher()
            || $user->hasQuranAssignment();
    }
}
