<?php

namespace App\Http\Controllers\Orangtua;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AttendanceClass;
use App\Models\AttendanceSubject;
use App\Models\Grade;
use App\Models\Student;
use App\Models\StudentQuranTarget;
use App\Models\TahfidzRecord;
use App\Services\AcademicYearContext;
use App\Services\QuranTargetService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class OrtuController extends Controller
{
    public function __construct(
        protected AcademicYearContext $academicYearContext,
        protected QuranTargetService $quranTargetService,
    ) {}

    public function children(): \Illuminate\Database\Eloquent\Collection
    {
        return auth()->user()
            ->students()
            ->with('classRoom')
            ->orderBy('name')
            ->get();
    }

    private function resolveStudent(?string $studentId): Student
    {
        $student = auth()->user()
            ->students()
            ->where('students.id', $studentId ?? '')
            ->with('classRoom')
            ->first();

        abort_unless($student, 403, 'Anda tidak memiliki akses ke siswa ini.');

        return $student;
    }

    public function switchChild(Request $request): RedirectResponse
    {
        $studentId = $request->input('student_id');
        $this->resolveStudent($studentId);

        session(['active_child_id' => $studentId]);

        return redirect()->route('ortu.dashboard');
    }

    public function index(): View
    {
        $children = $this->children();
        $child = $this->activeChild($children);

        $stats = null;
        if ($child) {
            $stats = $this->dashboardStats($child);
        }

        return view('orangtua.dashboard', compact('children', 'child', 'stats'));
    }

    private function activeChild($children): ?Student
    {
        if ($children->isEmpty()) {
            return null;
        }

        $activeId = session('active_child_id');
        $found = $children->firstWhere('id', $activeId);

        if (!$found) {
            session(['active_child_id' => $children->first()->id]);
            $found = $children->first();
        }

        return $found;
    }

    private function dashboardStats(Student $child): array
    {
        $ayId = $this->academicYearContext->id();
        $today = Carbon::today()->toDateString();

        $tahfidzQuery = TahfidzRecord::where('student_id', $child->id)
            ->when($ayId, fn ($q) => $q->where(function ($q2) use ($ayId) {
                $q2->where('academic_year_id', $ayId)->orWhereNull('academic_year_id');
            }));

        $tahfidz = (clone $tahfidzQuery)->get();

        $gradeQuery = Grade::where('student_id', $child->id)
            ->when($ayId, fn ($q) => $q->where(function ($q2) use ($ayId) {
                $q2->where('academic_year_id', $ayId)->orWhereNull('academic_year_id');
            }));

        $grades = $gradeQuery->with('subject', 'gradeType')->get();
        $average = $grades->isNotEmpty() ? round($grades->avg('score'), 1) : null;

        $subjectAttendance = AttendanceSubject::where('student_id', $child->id)
            ->when($ayId, fn ($q) => $q->where(function ($q2) use ($ayId) {
                $q2->where('academic_year_id', $ayId)->orWhereNull('academic_year_id');
            }))
            ->get();

        return [
            'tahfidz' => [
                'total_ayat' => $tahfidz->where('activity_type', '!=', null)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1),
                'ziadah' => $tahfidz->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->count(),
                'murajaah' => $tahfidz->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->count(),
                'avg_score' => $tahfidz->where('score', '>', 0)->avg('score') ? round($tahfidz->where('score', '>', 0)->avg('score'), 1) : null,
            ],
            'grades' => [
                'total' => $grades->count(),
                'average' => $average,
            ],
            'attendance' => [
                'total' => $subjectAttendance->count(),
                'hadir' => $subjectAttendance->where('status', AttendanceSubject::STATUS_HADIR)->count(),
                'izin' => $subjectAttendance->where('status', AttendanceSubject::STATUS_IZIN)->count(),
                'sakit' => $subjectAttendance->where('status', AttendanceSubject::STATUS_SAKIT)->count(),
                'alpa' => $subjectAttendance->where('status', AttendanceSubject::STATUS_ALPA)->count(),
            ],
        ];
    }

    public function absensiIndex(Request $request): View
    {
        $children = $this->children();
        $child = $this->resolveStudent(session('active_child_id') ?: $children->first()?->id);
        $month = $request->query('month', Carbon::today()->format('Y-m'));

        return view('orangtua.absensi', compact('children', 'child', 'month'));
    }

    public function absensiKelas(Request $request): View
    {
        $children = $this->children();
        $child = $this->resolveStudent(session('active_child_id') ?: $children->first()?->id);
        $ayId = $this->academicYearContext->id();

        $period = $request->query('period', 'bulan');
        [$from, $to] = $this->periodRange($period, $ayId, $child->school_id);

        $classRecords = AttendanceClass::where('student_id', $child->id)
            ->when($from && $to, fn ($q) => $q->whereDate('date', '>=', $from)->whereDate('date', '<=', $to))
            ->when($ayId, fn ($q) => $q->where(function ($q2) use ($ayId) {
                $q2->where('academic_year_id', $ayId)->orWhereNull('academic_year_id');
            }))
            ->with(['classRoom', 'recorder'])
            ->orderBy('date')
            ->get();

        $classSummary = [
            'total' => $classRecords->count(),
            'hadir' => $classRecords->where('status', AttendanceClass::STATUS_HADIR)->count(),
            'izin' => $classRecords->where('status', AttendanceClass::STATUS_IZIN)->count(),
            'sakit' => $classRecords->where('status', AttendanceClass::STATUS_SAKIT)->count(),
            'alpa' => $classRecords->where('status', AttendanceClass::STATUS_ALPA)->count(),
        ];

        return view('orangtua.absensi.kelas', compact('children', 'child', 'classRecords', 'classSummary', 'period', 'from', 'to'));
    }

    public function absensiMapel(Request $request): View
    {
        $children = $this->children();
        $child = $this->resolveStudent(session('active_child_id') ?: $children->first()?->id);
        $ayId = $this->academicYearContext->id();

        $period = $request->query('period', 'bulan');
        [$from, $to] = $this->periodRange($period, $ayId, $child->school_id);

        $records = AttendanceSubject::where('student_id', $child->id)
            ->when($from && $to, fn ($q) => $q->whereDate('date', '>=', $from)->whereDate('date', '<=', $to))
            ->when($ayId, fn ($q) => $q->where(function ($q2) use ($ayId) {
                $q2->where('academic_year_id', $ayId)->orWhereNull('academic_year_id');
            }))
            ->with(['subject', 'teacher'])
            ->orderBy('date')
            ->get();

        $summary = [
            'total' => $records->count(),
            'hadir' => $records->where('status', AttendanceSubject::STATUS_HADIR)->count(),
            'izin' => $records->where('status', AttendanceSubject::STATUS_IZIN)->count(),
            'sakit' => $records->where('status', AttendanceSubject::STATUS_SAKIT)->count(),
            'alpa' => $records->where('status', AttendanceSubject::STATUS_ALPA)->count(),
        ];

        return view('orangtua.absensi.mapel', compact('children', 'child', 'records', 'summary', 'period', 'from', 'to'));
    }

    private function periodRange(string $period, ?string $ayId, string $schoolId): array
    {
        $now = Carbon::now();
        $ay = $ayId ? AcademicYear::find($ayId) : null;

        switch ($period) {
            case 'pekan':
                return [$now->copy()->startOfWeek()->toDateString(), $now->copy()->toDateString()];
            case 'semester':
                if ($ay) {
                    $start = Carbon::parse($ay->start_date);
                    $end = Carbon::parse($ay->end_date);
                    $mid = $start->copy()->diffInMonths($end) > 0
                        ? $start->copy()->addMonths((int) round($start->diffInMonths($end) / 2))
                        : $end->copy();
                    if ($now->lessThan($mid)) {
                        return [$start->toDateString(), $mid->copy()->subDay()->toDateString()];
                    }
                    return [$mid->toDateString(), $now->copy()->toDateString()];
                }
                return [$now->copy()->subMonths(6)->startOfDay()->toDateString(), $now->copy()->toDateString()];
            case 'ta':
                if ($ay) {
                    return [Carbon::parse($ay->start_date)->toDateString(), Carbon::parse($ay->end_date)->toDateString()];
                }
                return [$now->copy()->startOfYear()->toDateString(), $now->copy()->endOfYear()->toDateString()];
            case 'bulan':
            default:
                return [$now->copy()->startOfMonth()->toDateString(), $now->copy()->toDateString()];
        }
    }

    public function nilai(Request $request): View
    {
        $children = $this->children();
        $child = $this->resolveStudent(session('active_child_id') ?: $children->first()?->id);
        $ayId = $this->academicYearContext->id();

        $years = AcademicYear::where('school_id', $child->school_id)->orderByDesc('start_date')->get();
        $selectedYear = $request->query('academic_year_id', $ayId ?? $years->first()?->id);

        $grades = Grade::where('student_id', $child->id)
            ->when($selectedYear, fn ($q) => $q->where(function ($q2) use ($selectedYear) {
                $q2->where('academic_year_id', $selectedYear)->orWhereNull('academic_year_id');
            }))
            ->with(['subject', 'gradeType'])
            ->orderBy('date')
            ->get();

        $bySubject = $grades->groupBy('subject_id')->map(function ($subjectGrades) {
            $subject = $subjectGrades->first()->subject;
            return [
                'subject' => $subject,
                'records' => $subjectGrades,
                'average' => round($subjectGrades->avg('score'), 1),
                'predikat' => $this->scorePredikat(round($subjectGrades->avg('score'))),
            ];
        });

        return view('orangtua.nilai', compact('children', 'child', 'grades', 'bySubject', 'years', 'selectedYear'));
    }

    private function scorePredikat(int $score): string
    {
        return match (true) {
            $score >= 90 => 'A',
            $score >= 75 => 'B',
            $score >= 60 => 'C',
            default => 'D',
        };
    }

    public function hafalan(Request $request): View
    {
        $children = $this->children();
        $child = $this->resolveStudent(session('active_child_id') ?: $children->first()?->id);

        $records = TahfidzRecord::where('student_id', $child->id)
            ->with('quranMaster', 'teacher')
            ->orderByDesc('recorded_date')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        $summary = [
            'total' => $records->count(),
            'ziadah' => $records->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->count(),
            'murajaah' => $records->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->count(),
            'ayat' => $records->where('activity_type', '!=', null)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1),
            'surahs' => $records->pluck('quran_master_id')->filter()->unique()->count(),
        ];

        $targets = StudentQuranTarget::where('student_id', $child->id)
            ->with('items.quranMaster')
            ->orderBy('target_date')
            ->get()
            ->map(fn ($t) => $this->quranTargetService->decorateTarget($t))
            ->all();

        return view('orangtua.hafalan', compact('children', 'child', 'records', 'summary', 'targets'));
    }

    public function hafalanChart(Request $request): JsonResponse
    {
        $children = $this->children();
        $child = $this->resolveStudent(session('active_child_id') ?: $children->first()?->id);
        $ayId = $this->academicYearContext->id();

        $filter = $request->query('filter', 'ta_init');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $now = Carbon::now();

        $query = TahfidzRecord::where('student_id', $child->id)
            ->with('quranMaster');

        if ($dateFrom && $dateTo) {
            $start = Carbon::parse($dateFrom)->startOfDay();
            $end = Carbon::parse($dateTo)->endOfDay();
            $filter = 'custom';
        } else {
            switch ($filter) {
                case 'pekan':
                    $start = $now->copy()->subWeek()->startOfDay();
                    $end = $now->copy()->endOfDay();
                    break;
                case 'pekan_lalu':
                    $start = $now->copy()->subWeeks(2)->startOfDay();
                    $end = $now->copy()->subWeek()->endOfDay();
                    break;
                case 'bulan':
                    $start = $now->copy()->subMonth()->startOfDay();
                    $end = $now->copy()->endOfDay();
                    break;
                case 'bulan_lalu':
                    $start = $now->copy()->subMonths(2)->startOfDay();
                    $end = $now->copy()->subMonth()->endOfDay();
                    break;
                case 'semester':
                    $start = $now->copy()->subMonths(6)->startOfDay();
                    $end = $now->copy()->endOfDay();
                    break;
                case 'semester_lalu':
                    $start = $now->copy()->subMonths(12)->startOfDay();
                    $end = $now->copy()->subMonths(6)->endOfDay();
                    break;
                case 'ta_init':
                    if ($ayId) {
                        $query->where('academic_year_id', $ayId);
                    }
                    $start = null;
                    $end = null;
                    break;
                case 'ta_lalu':
                    if ($ayId) {
                        $prevAy = AcademicYear::where('school_id', $child->school_id)
                            ->where('start_date', '<', Carbon::parse($this->academicYearContext->get()->start_date))
                            ->orderByDesc('start_date')
                            ->first();
                        if ($prevAy) {
                            $query->where('academic_year_id', $prevAy->id);
                        } else {
                            $query->whereRaw('0=1');
                        }
                    } else {
                        $query->whereRaw('0=1');
                    }
                    $start = null;
                    $end = null;
                    break;
                default:
                    $start = null;
                    $end = null;
            }
        }

        if ($start && $end) {
            $query->whereBetween('recorded_date', [$start, $end]);
        }

        $records = $query->orderBy('recorded_date')->get();

        $dates = $records->groupBy(fn ($r) => Carbon::parse($r->recorded_date)->format('Y-m-d'));
        $chartLabels = [];
        $chartAyatZiadah = [];
        $chartAyatMurajaah = [];
        $chartAvgScores = [];
        $chartCounts = [];
        $chartSurahs = [];

        if ($start && $end) {
            $current = $start->copy()->startOfDay();
            while ($current->lte($end)) {
                $key = $current->format('Y-m-d');
                $chartLabels[] = $current->format('d M');
                $dayRecords = $dates->get($key, collect());
                $chartAyatZiadah[] = $dayRecords->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1);
                $chartAyatMurajaah[] = $dayRecords->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1);
                $chartAvgScores[] = $dayRecords->isNotEmpty() ? round($dayRecords->where('activity_type', '!=', null)->avg('score'), 1) : null;
                $chartCounts[] = $dayRecords->count();
                $chartSurahs[] = $dayRecords->pluck('quran_master_id')->filter()->unique()->count();
                $current->addDay();
            }
        } else {
            foreach ($dates as $date => $dayRecords) {
                $chartLabels[] = Carbon::parse($date)->format('d M');
                $chartAyatZiadah[] = $dayRecords->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1);
                $chartAyatMurajaah[] = $dayRecords->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1);
                $chartAvgScores[] = $dayRecords->isNotEmpty() ? round($dayRecords->where('activity_type', '!=', null)->avg('score'), 1) : null;
                $chartCounts[] = $dayRecords->count();
                $chartSurahs[] = $dayRecords->pluck('quran_master_id')->filter()->unique()->count();
            }
        }

        return response()->json([
            'labels' => $chartLabels,
            'ayat_ziadah' => $chartAyatZiadah,
            'ayat_murajaah' => $chartAyatMurajaah,
            'avg_scores' => $chartAvgScores,
            'counts' => $chartCounts,
            'surahs' => $chartSurahs,
            'stats' => [
                'total' => $records->count(),
                'ziadah' => $records->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->count(),
                'murajaah' => $records->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->count(),
                'ziadah_ayat' => $records->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1),
                'murajaah_ayat' => $records->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1),
                'avg_score' => round($records->where('activity_type', '!=', null)->avg('score') ?? 0, 1),
                'surahs' => $records->pluck('quran_master_id')->filter()->unique()->count(),
            ],
        ]);
    }

    public function showChangePassword(): View
    {
        return view('orangtua.change-password');
    }

    public function changePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = $request->user();
        $user->update([
            'password' => Hash::make($validated['password']),
            'password_changed_at' => now(),
        ]);

        session()->forget('active_child_id');

        return redirect()->route('ortu.dashboard')->with('status', 'Password berhasil diubah. Silakan lanjutkan.');
    }
}
