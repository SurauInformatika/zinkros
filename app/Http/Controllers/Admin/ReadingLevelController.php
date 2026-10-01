<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuranReadingLevel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReadingLevelController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $levels = QuranReadingLevel::forSchool($user->school_id);

        return view('admin.reading-levels.index', compact('levels'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'kind' => 'required|in:' . implode(',', array_keys(QuranReadingLevel::TYPES)),
            'number' => 'required|integer|min:1|max:999',
            'label' => 'required|string|max:100',
            'pages' => 'required|integer|min:1|max:9999',
        ]);

        $exists = QuranReadingLevel::where('school_id', $user->school_id)
            ->where('kind', $validated['kind'])
            ->where('number', $validated['number'])
            ->exists();

        if ($exists) {
            return back()->with('error', "Jenjang {$validated['kind']} nomor {$validated['number']} sudah ada.");
        }

        $maxOrder = QuranReadingLevel::where('school_id', $user->school_id)->max('sort_order') ?? 0;

        QuranReadingLevel::create([
            'school_id' => $user->school_id,
            'kind' => $validated['kind'],
            'number' => $validated['number'],
            'label' => $validated['label'],
            'pages' => $validated['pages'],
            'sort_order' => $maxOrder + 1,
        ]);

        QuranReadingLevel::markSchoolCustomized($user->school_id);

        return back()->with('success', 'Jenjang baca berhasil ditambahkan.');
    }

    /**
     * Use the standard global catalog as the school's own starting point
     * (Al-Qur'an Jilid 1-6 + Juz 1-30). Schools then edit/remove as needed,
     * e.g. to switch to Iqra or UMMi.
     */
    public function loadDefaults(): RedirectResponse
    {
        $user = auth()->user();

        $global = QuranReadingLevel::whereNull('school_id')->get();
        $created = 0;
        $maxOrder = QuranReadingLevel::where('school_id', $user->school_id)->max('sort_order') ?? 0;

        foreach ($global as $level) {
            $exists = QuranReadingLevel::where('school_id', $user->school_id)
                ->where('kind', $level->kind)
                ->where('number', $level->number)
                ->exists();

            if ($exists) {
                continue;
            }

            QuranReadingLevel::create([
                'school_id' => $user->school_id,
                'kind' => $level->kind,
                'number' => $level->number,
                'label' => $level->label,
                'pages' => $level->pages,
                'sort_order' => ++$maxOrder,
            ]);

            $created++;
        }

        if ($created > 0) {
            QuranReadingLevel::markSchoolCustomized($user->school_id);
        }

        return back()->with(
            'success',
            $created > 0
                ? "Jenjang standar Al-Qur'an dimuat ($created jenjang). Ubah atau hapus sesuai metode baca sekolah."
                : 'Jenjang standar sudah tersedia.'
        );
    }

    public function update(Request $request, QuranReadingLevel $readingLevel): RedirectResponse
    {
        $user = auth()->user();

        if ($readingLevel->school_id !== $user->school_id) {
            abort(403);
        }

        $validated = $request->validate([
            'label' => 'required|string|max:100',
            'pages' => 'required|integer|min:1|max:9999',
        ]);

        $readingLevel->update($validated);

        return back()->with('success', 'Jenjang baca berhasil diperbarui.');
    }

    public function destroy(QuranReadingLevel $readingLevel): RedirectResponse
    {
        $user = auth()->user();

        [$mapping, $ownRows] = $this->ensureSchoolCatalog($user->school_id);

        $targetId = $readingLevel->school_id ? $readingLevel->id : ($mapping[$readingLevel->id] ?? null);

        if (! $targetId) {
            return back()->with('error', 'Jenjang tidak ditemukan.');
        }

        $target = $ownRows->firstWhere('id', $targetId);

        if (! $target || $target->school_id !== $user->school_id) {
            abort(403);
        }

        if ($target->tilawahRecords()->exists()) {
            return back()->with('error', 'Jenjang ini sudah dipakai di catatan tilawah dan tidak bisa dihapus.');
        }

        $target->delete();

        return back()->with('success', 'Jenjang baca berhasil dihapus.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|uuid',
        ]);

        $selected = QuranReadingLevel::whereIn('id', $validated['ids'])->get();

        [$mapping, $ownRows] = $this->ensureSchoolCatalog($user->school_id);

        $skipped = 0;
        $deleted = 0;

        foreach ($selected as $level) {
            $targetId = $level->school_id ? $level->id : ($mapping[$level->id] ?? null);

            if (! $targetId) {
                continue;
            }

            $target = $ownRows->firstWhere('id', $targetId);

            if (! $target || $target->school_id !== $user->school_id) {
                continue;
            }

            if ($target->tilawahRecords()->exists()) {
                $skipped++;
                continue;
            }

            $target->delete();
            $deleted++;
        }

        $msg = "$deleted jenjang berhasil dihapus.";
        if ($skipped > 0) {
            $msg .= " $skipped dilewati (sudah dipakai di catatan tilawah).";
        }

        if ($deleted === 0) {
            return back()->with('error', 'Tidak ada jenjang yang bisa dihapus (sudah dipakai di catatan tilawah).');
        }

        return back()->with('success', $msg);
    }

    /**
     * Make the school's effective catalog explicit as school-owned rows.
     *
     * The page shows the effective catalog (global defaults + school-owned).
     * When the user deletes any of those rows, we first copy the whole
     * current effective catalog into school-owned rows (so the remaining rows
     * keep working and deletions can't touch the shared global catalog), then
     * mark the school as customizing its catalog.
     *
     * @return array{0: array<string, string>, 1: \Illuminate\Support\Collection}
     */
    private function ensureSchoolCatalog(string $schoolId): array
    {
        $mapping = [];

        foreach (QuranReadingLevel::forSchool($schoolId) as $level) {
            if ($level->school_id) {
                $ownRows[] = $level;
                continue;
            }

            $own = QuranReadingLevel::where('school_id', $schoolId)
                ->where('kind', $level->kind)
                ->where('number', $level->number)
                ->first();

            if (! $own) {
                $own = QuranReadingLevel::create([
                    'school_id' => $schoolId,
                    'kind' => $level->kind,
                    'number' => $level->number,
                    'label' => $level->label,
                    'pages' => $level->pages,
                    'sort_order' => $level->sort_order,
                ]);
            }

            $mapping[$level->id] = $own->id;
            $ownRows[] = $own;
        }

        QuranReadingLevel::markSchoolCustomized($schoolId);

        return [$mapping, collect($ownRows ?? [])];
    }
}