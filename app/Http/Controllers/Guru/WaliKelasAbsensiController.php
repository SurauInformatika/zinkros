<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\AttendanceClass;
use App\Models\ClassRoom;
use App\Models\Student;
use App\Services\AcademicYearContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class WaliKelasAbsensiController extends Controller
{
    public function __construct(protected AcademicYearContext $academicYearContext) {}

    public function index(): View
    {
        $user = auth()->user();
        $today = Carbon::today()->toDateString();

        $waliClasses = ClassRoom::whereHas('homerooms', fn ($q) => $q->where('user_id', $user->id))
            ->withCount(['students', 'attendanceClasses' => function ($q) use ($today) {
                $q->where('date', $today);
                if ($this->academicYearContext->exists()) {
                    $q->where(function ($q2) {
                        $q2->where('academic_year_id', $this->academicYearContext->id())
                            ->orWhereNull('academic_year_id');
                    });
                }
            }])
            ->with(['attendanceClasses' => function ($q) use ($today) {
                $q->where('date', $today);
                if ($this->academicYearContext->exists()) {
                    $q->where(function ($q2) {
                        $q2->where('academic_year_id', $this->academicYearContext->id())
                            ->orWhereNull('academic_year_id');
                    });
                }
            }])
            ->get()
            ->map(function ($class) {
                $todayAbsensi = $class->attendanceClasses;
                return [
                    'class' => $class,
                    'total_students' => $class->students_count,
                    'has_today' => $todayAbsensi->isNotEmpty(),
                    'hadir' => $todayAbsensi->where('status', 'HADIR')->count(),
                    'izin' => $todayAbsensi->where('status', 'IZIN')->count(),
                    'sakit' => $todayAbsensi->where('status', 'SAKIT')->count(),
                    'alpa' => $todayAbsensi->where('status', 'ALPA')->count(),
                ];
            });

        return view('guru.absensi-kelas.index', compact('waliClasses', 'today'));
    }

    public function create(Request $request): View
    {
        $user = auth()->user();
        $classId = $request->query('class_id');

        if (!$classId) {
            return redirect()->route('guru.absensi-kelas.index')
                ->with('error', 'Pilih kelas terlebih dahulu.');
        }

        $classRoom = ClassRoom::where('id', $classId)
            ->whereHas('homerooms', fn ($q) => $q->where('user_id', $user->id))
            ->first();

        if (!$classRoom) {
            return redirect()->route('guru.absensi-kelas.index')
                ->with('error', 'Anda bukan wali kelas untuk kelas ini.');
        }

        $students = Student::where('class_id', $classId)->orderBy('name')->get();
        $date = $request->query('date', Carbon::today()->toDateString());

        $existingAbsensi = AttendanceClass::where('class_id', $classId)
            ->where('date', $date)
            ->when($this->academicYearContext->exists(), function ($q) {
                $q->where(function ($q2) {
                    $q2->where('academic_year_id', $this->academicYearContext->id())
                        ->orWhereNull('academic_year_id');
                });
            })
            ->pluck('status', 'student_id')
            ->toArray();

        $hasSubmitted = count($existingAbsensi) > 0;

        return view('guru.absensi-kelas.create', compact(
            'classRoom', 'students', 'date', 'existingAbsensi', 'hasSubmitted'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $schoolId = $user->school_id;

        $validated = $request->validate([
            'class_id' => 'required|uuid',
            'date' => 'required|date',
            'absensi' => 'required|array',
            'absensi.*.student_id' => 'required|uuid',
            'absensi.*.status' => 'required|in:HADIR,IZIN,SAKIT,ALPA',
            'absensi.*.notes' => 'nullable|string|max:255',
        ]);

        $classRoom = ClassRoom::where('id', $validated['class_id'])
            ->whereHas('homerooms', fn ($q) => $q->where('user_id', $user->id))
            ->first();

        if (!$classRoom) {
            return back()->with('error', 'Anda bukan wali kelas untuk kelas ini.');
        }

        foreach ($validated['absensi'] as $item) {
            $match = [
                'class_id' => $validated['class_id'],
                'student_id' => $item['student_id'],
                'date' => $validated['date'],
            ];

            $data = [
                'school_id' => $schoolId,
                'recorded_by' => $user->id,
                'status' => $item['status'],
                'notes' => $item['notes'] ?? null,
            ];

            if ($this->academicYearContext->exists()) {
                $match['academic_year_id'] = $this->academicYearContext->id();
                $data['academic_year_id'] = $this->academicYearContext->id();
            }

            $existing = AttendanceClass::where('class_id', $match['class_id'])
                ->where('student_id', $match['student_id'])
                ->where('date', $match['date'])
                ->first();

            if ($existing) {
                $existing->update($data + ($this->academicYearContext->exists() ? ['academic_year_id' => $this->academicYearContext->id()] : []));
            } else {
                AttendanceClass::create($match + $data);
            }
        }

        return redirect()->route('guru.absensi-kelas.index')
            ->with('success', 'Absensi kelas berhasil disimpan untuk ' . $validated['date']);
    }

    public function rekap(Request $request): View
    {
        $user = auth()->user();

        $waliClasses = ClassRoom::whereHas('homerooms', fn ($q) => $q->where('user_id', $user->id))->get();

        $classId = $request->query('class_id', $waliClasses->first()?->id);
        $dateFrom = $request->query('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->query('date_to', Carbon::now()->toDateString());

        $students = collect();
        $rekapData = collect();
        $dates = collect();
        $classRoom = null;

        if ($classId) {
            $classRoom = ClassRoom::where('id', $classId)
                ->whereHas('homerooms', fn ($q) => $q->where('user_id', $user->id))
                ->first();

            if ($classRoom) {
                $students = Student::where('class_id', $classId)->orderBy('name')->get();

                $dates = collect(Carbon::parse($dateFrom)->daysUntil(Carbon::parse($dateTo)->addDay()))
                    ->map(fn ($d) => $d->toDateString())
                    ->values()
                    ->toArray();

                $absensiRecords = AttendanceClass::where('class_id', $classId)
                    ->whereBetween('date', [$dateFrom, $dateTo])
                    ->when($this->academicYearContext->exists(), function ($q) {
                        $q->where(function ($q2) {
                            $q2->where('academic_year_id', $this->academicYearContext->id())
                                ->orWhereNull('academic_year_id');
                        });
                    })
                    ->get()
                    ->keyBy(fn ($r) => $r->student_id . '_' . $r->date);

                $rekapData = $absensiRecords;

                $totalRecords = $rekapData->count();
                $stats = [
                    'hadir' => $rekapData->where('status', 'HADIR')->count(),
                    'izin' => $rekapData->where('status', 'IZIN')->count(),
                    'sakit' => $rekapData->where('status', 'SAKIT')->count(),
                    'alpa' => $rekapData->where('status', 'ALPA')->count(),
                    'total' => $totalRecords,
                    'persentase_hadir' => $totalRecords > 0 ? round($rekapData->where('status', 'HADIR')->count() / $totalRecords * 100, 1) : 0,
                    'persentase_izin' => $totalRecords > 0 ? round($rekapData->where('status', 'IZIN')->count() / $totalRecords * 100, 1) : 0,
                    'persentase_sakit' => $totalRecords > 0 ? round($rekapData->where('status', 'SAKIT')->count() / $totalRecords * 100, 1) : 0,
                    'persentase_alpa' => $totalRecords > 0 ? round($rekapData->where('status', 'ALPA')->count() / $totalRecords * 100, 1) : 0,
                ];

                $studentStats = $rekapData->groupBy('student_id')->map(function ($records, $studentId) {
                    $total = $records->count();
                    $hadir = $records->where('status', 'HADIR')->count();
                    $sakit = $records->where('status', 'SAKIT')->count();
                    $izin = $records->where('status', 'IZIN')->count();
                    $alpa = $records->where('status', 'ALPA')->count();
                    return [
                        'hadir' => $hadir,
                        'sakit' => $sakit,
                        'izin' => $izin,
                        'alpa' => $alpa,
                        'total' => $total,
                        'persentase_hadir' => $total > 0 ? round($hadir / $total * 100, 1) : 0,
                        'persentase_sakit' => $total > 0 ? round($sakit / $total * 100, 1) : 0,
                        'persentase_izin' => $total > 0 ? round($izin / $total * 100, 1) : 0,
                        'persentase_alpa' => $total > 0 ? round($alpa / $total * 100, 1) : 0,
                    ];
                });
            }
        }

        return view('guru.absensi-kelas.rekap', compact(
            'waliClasses', 'classRoom', 'students', 'dates', 'rekapData', 'dateFrom', 'dateTo', 'stats', 'studentStats'
        ));
    }

    public function studentAttendance(Request $request, string $studentId): View|RedirectResponse
    {
        $user = auth()->user();
        $classId = $request->query('class_id');
        $dateFrom = $request->query('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->query('date_to', Carbon::now()->toDateString());

        $student = Student::with('classRoom')->findOrFail($studentId);

        if (!$classId || $student->class_id !== $classId) {
            return redirect()->route('guru.absensi-kelas.rekap')
                ->with('error', 'Siswa tidak ditemukan di kelas ini.');
        }

        $classRoom = ClassRoom::where('id', $classId)
            ->whereHas('homerooms', fn ($q) => $q->where('user_id', $user->id))
            ->first();

        if (!$classRoom) {
            return redirect()->route('guru.absensi-kelas.rekap')
                ->with('error', 'Anda bukan wali kelas untuk kelas ini.');
        }

        $records = AttendanceClass::where('student_id', $studentId)
            ->where('class_id', $classId)
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->when($this->academicYearContext->exists(), function ($q) {
                $q->where(function ($q2) {
                    $q2->where('academic_year_id', $this->academicYearContext->id())
                        ->orWhereNull('academic_year_id');
                });
            })
            ->orderByDesc('date')
            ->get();

        $total = $records->count();
        $hadir = $records->where('status', 'HADIR')->count();
        $sakit = $records->where('status', 'SAKIT')->count();
        $izin = $records->where('status', 'IZIN')->count();
        $alpa = $records->where('status', 'ALPA')->count();

        $stats = [
            'total' => $total,
            'hadir' => $hadir,
            'sakit' => $sakit,
            'izin' => $izin,
            'alpa' => $alpa,
            'persentase_hadir' => $total > 0 ? round($hadir / $total * 100, 1) : 0,
            'persentase_sakit' => $total > 0 ? round($sakit / $total * 100, 1) : 0,
            'persentase_izin' => $total > 0 ? round($izin / $total * 100, 1) : 0,
            'persentase_alpa' => $total > 0 ? round($alpa / $total * 100, 1) : 0,
        ];

        return view('guru.absensi-kelas.student', compact(
            'student', 'classRoom', 'records', 'stats', 'dateFrom', 'dateTo'
        ));
    }
}
