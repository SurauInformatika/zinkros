<?php

namespace App\Http\Controllers\Murid;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\StudentQuranTarget;
use App\Models\TahfidzRecord;
use App\Services\AcademicYearContext;
use App\Services\QuranTargetService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MuridController extends Controller
{
    public function __construct(
        protected AcademicYearContext $academicYearContext,
        protected QuranTargetService $quranTargetService,
    ) {}

    public function dashboard(): View
    {
        $user = auth()->user();

        return view('murid.dashboard', [
            'name' => $user->name,
            'school' => $user->school,
            'roleLabel' => $user->school?->roleLabel($user->role) ?? 'Murid',
        ]);
    }

    public function hafalan(Request $request): View
    {
        $student = $this->resolveStudent();

        $records = TahfidzRecord::where('student_id', $student->id)
            ->with('quranMaster', 'teacher')
            ->orderByDesc('recorded_date')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        $targets = StudentQuranTarget::where('student_id', $student->id)
            ->with('items.quranMaster')
            ->orderBy('target_date')
            ->get()
            ->map(fn ($t) => $this->quranTargetService->decorateTarget($t))
            ->all();

        $summary = [
            'total' => $records->count(),
            'ziadah' => $records->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->count(),
            'murajaah' => $records->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->count(),
            'ayat' => $records->where('activity_type', '!=', null)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1),
            'surahs' => $records->pluck('quran_master_id')->filter()->unique()->count(),
        ];

        return view('murid.hafalan', compact('student', 'records', 'targets', 'summary'));
    }

    public function hafalanChart(Request $request): JsonResponse
    {
        $student = $this->resolveStudent();
        $ayId = $this->academicYearContext->id();

        $filter = $request->query('filter', 'ta_init');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
        $now = Carbon::now();

        $query = TahfidzRecord::where('student_id', $student->id)->with('quranMaster');

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
                case 'bulan':
                    $start = $now->copy()->subMonth()->startOfDay();
                    $end = $now->copy()->endOfDay();
                    break;
                case 'semester':
                    $start = $now->copy()->subMonths(6)->startOfDay();
                    $end = $now->copy()->endOfDay();
                    break;
                case 'ta_init':
                    if ($ayId) {
                        $query->where('academic_year_id', $ayId);
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

        if ($start && $end) {
            $current = $start->copy()->startOfDay();
            while ($current->lte($end)) {
                $key = $current->format('Y-m-d');
                $chartLabels[] = $current->format('d M');
                $dayRecords = $dates->get($key, collect());
                $chartAyatZiadah[] = $dayRecords->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1);
                $chartAyatMurajaah[] = $dayRecords->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1);
                $chartAvgScores[] = $dayRecords->isNotEmpty() ? round($dayRecords->where('activity_type', '!=', null)->avg('score'), 1) : null;
                $current->addDay();
            }
        } else {
            foreach ($dates as $key => $dayRecords) {
                $chartLabels[] = Carbon::parse($key)->format('d M');
                $chartAyatZiadah[] = $dayRecords->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1);
                $chartAyatMurajaah[] = $dayRecords->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1);
                $chartAvgScores[] = $dayRecords->isNotEmpty() ? round($dayRecords->where('activity_type', '!=', null)->avg('score'), 1) : null;
            }
        }

        return response()->json([
            'labels' => $chartLabels,
            'ayat_ziadah' => $chartAyatZiadah,
            'ayat_murajaah' => $chartAyatMurajaah,
            'avg_scores' => $chartAvgScores,
            'stats' => [
                'total' => $records->count(),
                'ziadah' => $records->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->count(),
                'murajaah' => $records->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->count(),
                'ziadah_ayat' => $records->where('activity_type', TahfidzRecord::TYPE_ZIADAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1),
                'murajaah_ayat' => $records->where('activity_type', TahfidzRecord::TYPE_MURAJAAH)->sum(fn ($r) => $r->ayat_end - $r->ayat_start + 1),
                'avg_score' => round($records->where('activity_type', '!=', null)->avg('score') ?? 0, 1),
            ],
        ]);
    }

    private function resolveStudent()
    {
        $student = auth()->user()->student()
            ->with('classRoom')
            ->first();

        if (!$student) {
            abort(403, 'Akun murid ini belum terhubung dengan data siswa.');
        }

        return $student;
    }
}
