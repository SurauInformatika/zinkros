<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use App\Models\AcademicCalendar;
use App\Models\ClassRoom;
use App\Models\Notification;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KepsekController extends Controller
{
    public function dashboard(): View
    {
        $user = auth()->user();
        $schoolId = $user->school_id;

        $pendingCount = AcademicCalendar::where('school_id', $schoolId)
            ->where('status', 'pending')->count();
        $unreadNotif = Notification::where('user_id', $user->id)->unread()->count();

        $stats = [
            'siswa' => Student::where('school_id', $schoolId)->count(),
            'guru' => User::where('school_id', $schoolId)->where('role', 'guru')->count(),
            'kelas' => ClassRoom::where('school_id', $schoolId)->count(),
            'pending' => $pendingCount,
            'unread_notif' => $unreadNotif,
        ];

        $recentNotifications = Notification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('kepsek.dashboard', compact('stats', 'recentNotifications'));
    }

    public function kaldik(): View
    {
        $user = auth()->user();
        $calendars = AcademicCalendar::where('school_id', $user->school_id)
            ->with('academicYear', 'creator:id,name')
            ->orderByDesc('created_at')
            ->get();

        return view('kepsek.kaldik', compact('calendars'));
    }

    public function approve(AcademicCalendar $kaldik): RedirectResponse
    {
        $user = auth()->user();
        if ($kaldik->school_id !== $user->school_id) abort(403);
        if (!$kaldik->isPending()) return back()->with('error', 'Hanya kalender PENDING yang bisa di-approve.');

        $kaldik->update([
            'status' => AcademicCalendar::STATUS_FINAL,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        // If this is a revision, archive the superseded final version
        if ($kaldik->parent_version_id) {
            $parent = AcademicCalendar::find($kaldik->parent_version_id);
            if ($parent && $parent->isFinal()) {
                $parent->update(['status' => AcademicCalendar::STATUS_ARCHIVED]);
            }
        }

        // Notify WAKAKUR who submitted
        if ($kaldik->created_by) {
            Notification::create([
                'school_id' => $user->school_id,
                'user_id'   => $kaldik->created_by,
                'type'      => Notification::TYPE_KALDIK_APPROVED,
                'data'      => [
                    'calendar_id' => $kaldik->id,
                    'calendar_name' => $kaldik->name,
                    'approved_by' => $user->name,
                ],
            ]);
        }

        // Mark approval notification as read
        Notification::where('user_id', $user->id)
            ->where('type', Notification::TYPE_KALDIK_APPROVAL)
            ->whereJsonContains('data->calendar_id', $kaldik->id)
            ->update(['read_at' => now()]);

        return back()->with('success', 'Kalender "' . $kaldik->name . '" berhasil di-approve dan menjadi FINAL.');
    }

    public function reject(Request $request, AcademicCalendar $kaldik): RedirectResponse
    {
        $user = auth()->user();
        if ($kaldik->school_id !== $user->school_id) abort(403);
        if (!$kaldik->isPending()) return back()->with('error', 'Hanya kalender PENDING yang bisa direject.');

        $validated = $request->validate([
            'reject_reason' => 'required|string|max:1000',
        ]);

        $kaldik->update([
            'status' => AcademicCalendar::STATUS_DRAFT,
            'reject_reason' => $validated['reject_reason'],
        ]);

        // Notify WAKAKUR who submitted
        if ($kaldik->created_by) {
            Notification::create([
                'school_id' => $user->school_id,
                'user_id'   => $kaldik->created_by,
                'type'      => Notification::TYPE_KALDIK_REJECTED,
                'data'      => [
                    'calendar_id' => $kaldik->id,
                    'calendar_name' => $kaldik->name,
                    'rejected_by' => $user->name,
                    'reason' => $validated['reject_reason'],
                ],
            ]);
        }

        // Mark approval notification as read
        Notification::where('user_id', $user->id)
            ->where('type', Notification::TYPE_KALDIK_APPROVAL)
            ->whereJsonContains('data->calendar_id', $kaldik->id)
            ->update(['read_at' => now()]);

        return back()->with('success', 'Kalender "' . $kaldik->name . '" ditolak dan dikembalikan ke DRAFT.');
    }

    public function markNotificationRead(Notification $notification): RedirectResponse
    {
        $user = auth()->user();
        if ($notification->user_id !== $user->id) abort(403);
        $notification->markAsRead();
        return back();
    }

    public function markAllRead(): RedirectResponse
    {
        $user = auth()->user();
        Notification::where('user_id', $user->id)->unread()->update(['read_at' => now()]);
        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    public function classes(): View
    {
        $classes = ClassRoom::with(['walis:id,name'])
            ->withCount('students')
            ->orderBy('grade_level')
            ->orderBy('class_name')
            ->get();

        return view('kepsek.classes', compact('classes'));
    }

    public function classDetail(ClassRoom $kelas): View
    {
        $kelas->load('walis:id,name');
        $students = $kelas->students()->orderBy('name')->get();

        return view('kepsek.class-detail', compact('kelas', 'students'));
    }

    public function students(Request $request): View
    {
        $students = Student::with(['classRoom:id,class_name,grade_level'])
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q2) use ($request) {
                $q2->where('name', 'like', "%{$request->search}%")
                    ->orWhere('nis', 'like', "%{$request->search}%")
                    ->orWhere('nisn', 'like', "%{$request->search}%");
            }))
            ->when($request->filled('class_id'), fn ($q) => $q->where('class_id', $request->class_id))
            ->when($request->filled('gender'), fn ($q) => $q->where('gender', $request->gender))
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        $classes = ClassRoom::query()->orderBy('grade_level')->orderBy('class_name')->get();

        return view('kepsek.students', compact('students', 'classes'));
    }
}
