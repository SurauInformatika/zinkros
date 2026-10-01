<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GradeType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GradeTypeController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $gradeTypes = GradeType::where('school_id', $user->school_id)
            ->ordered()
            ->get();

        return view('admin.grade-types.index', compact('gradeTypes'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50|alpha_dash',
            'weight' => 'required|numeric|between:0,100',
        ]);

        $exists = GradeType::where('school_id', $user->school_id)
            ->where('code', $validated['code'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Kode tipe nilai "' . $validated['code'] . '" sudah digunakan.');
        }

        $maxOrder = GradeType::where('school_id', $user->school_id)->max('sort_order') ?? 0;

        GradeType::create([
            'school_id' => $user->school_id,
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'weight' => $validated['weight'],
            'sort_order' => $maxOrder + 1,
            'is_active' => true,
        ]);

        return back()->with('success', 'Tipe nilai berhasil ditambahkan.');
    }

    /**
     * The standard set of grade types offered as a starting template. Admins
     * can load these then edit or remove the ones they do not need.
     */
    public const TEMPLATE = [
        ['name' => 'Tugas', 'code' => 'TGS', 'weight' => 15],
        ['name' => 'Ulangan Harian', 'code' => 'UH', 'weight' => 25],
        ['name' => 'Penilaian Tengah Semester', 'code' => 'PTS', 'weight' => 20],
        ['name' => 'Penilaian Akhir Semester', 'code' => 'PAS', 'weight' => 25],
        ['name' => 'Praktek', 'code' => 'PRK', 'weight' => 15],
    ];

    public function template()
    {
        $user = auth()->user();

        $existingCodes = GradeType::where('school_id', $user->school_id)
            ->pluck('code')
            ->flip();

        $count = 0;
        $maxOrder = GradeType::where('school_id', $user->school_id)->max('sort_order') ?? 0;

        foreach (self::TEMPLATE as $item) {
            if ($existingCodes->has($item['code'])) {
                continue;
            }

            GradeType::create([
                'school_id' => $user->school_id,
                'code' => $item['code'],
                'name' => $item['name'],
                'weight' => $item['weight'],
                'sort_order' => ++$maxOrder,
                'is_active' => true,
            ]);

            $count++;
        }

        return back()->with(
            'success',
            $count > 0
                ? "Template standar berhasil ditambahkan ($count tipe). Silakan edit atau hapus yang tidak dibutuhkan."
                : 'Semua tipe template sudah tersedia.'
        );
    }

    public function update(Request $request, GradeType $gradeType)
    {
        $user = auth()->user();

        if ($gradeType->school_id !== $user->school_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'weight' => 'required|numeric|between:0,100',
        ]);

        $gradeType->update($validated);

        return back()->with('success', 'Tipe nilai berhasil diperbarui.');
    }

    public function toggle(GradeType $gradeType)
    {
        $user = auth()->user();

        if ($gradeType->school_id !== $user->school_id) {
            abort(403);
        }

        $gradeType->update(['is_active' => !$gradeType->is_active]);

        return back()->with('success', 'Tipe nilai berhasil ' . ($gradeType->is_active ? 'diaktifkan' : 'dinonaktifkan') . '.');
    }

    public function reorder(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|uuid',
        ]);

        foreach ($request->ids as $index => $id) {
            GradeType::where('id', $id)
                ->where('school_id', $user->school_id)
                ->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    public function destroy(GradeType $gradeType)
    {
        $user = auth()->user();

        if ($gradeType->school_id !== $user->school_id) {
            abort(403);
        }

        $gradeType->delete();

        return back()->with('success', 'Tipe nilai berhasil dihapus.');
    }
}
