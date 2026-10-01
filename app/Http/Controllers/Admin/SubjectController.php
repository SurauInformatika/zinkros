<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Scopes\SchoolScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubjectController extends Controller
{
    /**
     * Standard Indonesian subjects offered as a starting template. Admins can
     * load these then edit or delete the ones they do not need. Type is either
     * Subject::TYPE_GENERAL or Subject::TYPE_QURAN.
     */
    public const TEMPLATE = [
        ['name' => 'Pendidikan Agama Islam dan Budi Pekerti', 'type' => Subject::TYPE_GENERAL],
        ['name' => 'Pendidikan Pancasila', 'type' => Subject::TYPE_GENERAL],
        ['name' => 'Bahasa Indonesia', 'type' => Subject::TYPE_GENERAL],
        ['name' => 'Matematika', 'type' => Subject::TYPE_GENERAL],
        ['name' => 'Ilmu Pengetahuan Alam dan Sosial', 'type' => Subject::TYPE_GENERAL],
        ['name' => 'Bahasa Inggris', 'type' => Subject::TYPE_GENERAL],
        ['name' => 'Seni Budaya', 'type' => Subject::TYPE_GENERAL],
        ['name' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan', 'type' => Subject::TYPE_GENERAL],
        ['name' => 'Informatika', 'type' => Subject::TYPE_GENERAL],
        ['name' => 'Bahasa Arab', 'type' => Subject::TYPE_GENERAL],
        ['name' => 'Tahfizh', 'type' => Subject::TYPE_QURAN],
        ['name' => 'Al-Quran', 'type' => Subject::TYPE_QURAN],
    ];

    public function template(): RedirectResponse
    {
        $schoolId = auth()->user()->school_id;

        $existing = Subject::withoutGlobalScope(\App\Models\Scopes\SchoolScope::class)
            ->where('school_id', $schoolId)
            ->pluck('name')
            ->flip();

        $count = 0;
        foreach (self::TEMPLATE as $item) {
            if ($existing->has($item['name'])) {
                continue;
            }

            Subject::create([
                'school_id' => $schoolId,
                'name' => $item['name'],
                'type' => $item['type'],
            ]);

            $count++;
        }

        return back()->with(
            'status',
            $count > 0
                ? "Template mapel standar berhasil ditambahkan ($count mapel). Silakan edit atau hapus yang tidak dibutuhkan."
                : 'Semua mapel template sudah tersedia.'
        );
    }

    public function index(): View
    {
        $subjects = Subject::query()
            ->withCount(['assignedTeachers', 'classes'])
            ->orderBy('type')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.subject.index', compact('subjects'));
    }

    public function create(): View
    {
        return view('admin.subject.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', $this->uniqueName()],
            'type' => ['required', 'in:'.Subject::TYPE_GENERAL.','.Subject::TYPE_QURAN],
        ]);

        Subject::create($validated);

        return redirect()
            ->route('admin.subject.index')
            ->with('status', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(Subject $subject): View
    {
        return view('admin.subject.edit', compact('subject'));
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', $this->uniqueName($subject->id)],
            'type' => ['required', 'in:'.Subject::TYPE_GENERAL.','.Subject::TYPE_QURAN],
        ]);

        $subject->update($validated);

        return redirect()
            ->route('admin.subject.index')
            ->with('status', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $used = $subject->assignedTeachers()->exists()
            || $subject->classes()->exists()
            || $subject->attendanceSubjects()->exists()
            || $subject->grades()->exists();

        if ($used) {
            return back()->with('error', 'Mapel tidak dapat dihapus karena sudah digunakan pada guru/kelas.');
        }

        $subject->delete();

        return redirect()
            ->route('admin.subject.index')
            ->with('status', 'Mata pelajaran berhasil dihapus.');
    }

    public function destroyMany(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string'],
        ]);

        $deleted = 0;
        $skipped = 0;

        foreach (Subject::whereIn('id', $validated['ids'])->get() as $subject) {
            $used = $subject->assignedTeachers()->exists()
                || $subject->classes()->exists()
                || $subject->attendanceSubjects()->exists()
                || $subject->grades()->exists();

            if ($used) {
                $skipped++;
                continue;
            }

            $subject->delete();
            $deleted++;
        }

        $msg = "$deleted mata pelajaran berhasil dihapus.";
        if ($skipped > 0) {
            $msg .= " $skipped dilewati karena sudah digunakan pada guru/kelas.";
        }

        return redirect()
            ->route('admin.subject.index')
            ->with('status', $msg);
    }

    private function uniqueName(?string $ignoreId = null): Unique
    {
        return Rule::unique('subjects')
            ->where('school_id', auth()->user()->school_id)
            ->ignore($ignoreId);
    }
}
