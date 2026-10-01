<?php

namespace App\Services;

use App\Models\QuranTargetTemplate;
use App\Models\Student;
use App\Models\StudentQuranTarget;
use App\Models\StudentQuranTargetItem;
use App\Models\TahfidzRecord;
use App\Models\User;

class QuranTargetService
{
    /**
     * Hitung jumlah ayat yang sudah dihafal secara berurutan untuk satu surah.
     * Hanya record ZIADAH yang dihitung sebagai hafalan baru.
     * Progres dihitung berurutan (sequential) mulai dari ayat_start target.
     */
    public function sequentialAyatForSurah(string $studentId, string $quranMasterId, int $ayatStart, int $ayatEnd): int
    {
        $records = TahfidzRecord::where('student_id', $studentId)
            ->where('quran_master_id', $quranMasterId)
            ->where('activity_type', TahfidzRecord::TYPE_ZIADAH)
            ->orderBy('ayat_start')
            ->get(['ayat_start', 'ayat_end']);

        $cursor = $ayatStart;
        foreach ($records as $record) {
            $start = max((int) $record->ayat_start, $ayatStart);
            if ($start > $cursor) {
                break;
            }
            $end = min((int) $record->ayat_end, $ayatEnd);
            if ($end >= $cursor) {
                $cursor = $end + 1;
            }
        }

        $completed = $cursor - $ayatStart;
        $total = $ayatEnd - $ayatStart + 1;

        return max(0, min($completed, $total));
    }

    /**
     * Hitung progres satu target.
     *
     * @return array{total_ayat:int, completed_ayat:int, percent:float, items:array<int,array>}
     */
    public function targetProgress(StudentQuranTarget $target): array
    {
        $totalAyat = 0;
        $completedAyat = 0;
        $items = [];

        foreach ($target->items as $item) {
            $itemTotal = $item->totalAyat();
            $itemCompleted = $this->sequentialAyatForSurah(
                $target->student_id,
                $item->quran_master_id,
                $item->ayat_start,
                $item->ayat_end,
            );
            $itemPercent = $itemTotal > 0 ? round($itemCompleted / $itemTotal * 100, 1) : 0;

            $totalAyat += $itemTotal;
            $completedAyat += $itemCompleted;

            $items[] = [
                'id' => $item->id,
                'quran_master_id' => $item->quran_master_id,
                'surah_number' => $item->quranMaster?->surah_number,
                'surah_name' => $item->quranMaster?->surah_name,
                'ayat_start' => $item->ayat_start,
                'ayat_end' => $item->ayat_end,
                'total_ayat' => $itemTotal,
                'completed_ayat' => $itemCompleted,
                'percent' => $itemPercent,
            ];
        }

        $percent = $totalAyat > 0 ? round($completedAyat / $totalAyat * 100, 1) : 0;

        return [
            'total_ayat' => $totalAyat,
            'completed_ayat' => $completedAyat,
            'percent' => $percent,
            'items' => $items,
        ];
    }

    /**
     * Bangun payload progres untuk dirender di view.
     */
    public function decorateTarget(StudentQuranTarget $target): array
    {
        if (!$target->relationLoaded('items') || !$target->items->every(fn ($i) => $i->relationLoaded('quranMaster'))) {
            $target->load('items.quranMaster');
        }

        $progress = $this->targetProgress($target);

        $status = 'on_track';
        if ($progress['percent'] >= 100) {
            $status = 'completed';
        } elseif ($target->isOverdue()) {
            $status = 'overdue';
        }

        return array_merge($progress, [
            'id' => $target->id,
            'title' => $target->title,
            'target_date' => $target->target_date,
            'is_active' => $target->is_active,
            'status' => $status,
            'days_remaining' => $target->daysRemaining(),
        ]);
    }

    /**
     * Terapkan template ke seorang siswa, hasilkan target + items.
     */
    public function applyTemplateToStudent(Student $student, QuranTargetTemplate $template, User $teacher): StudentQuranTarget
    {
        $target = StudentQuranTarget::create([
            'school_id' => $student->school_id,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'title' => $template->title,
            'target_date' => $template->target_date,
        ]);

        foreach ($template->items as $item) {
            StudentQuranTargetItem::create([
                'target_id' => $target->id,
                'quran_master_id' => $item->quran_master_id,
                'ayat_start' => $item->ayat_start,
                'ayat_end' => $item->ayat_end,
            ]);
        }

        return $target->load('items.quranMaster');
    }

    /**
     * Cari template universal (grade_level null) untuk sekolah.
     * Jika ditemukan, otomatis terapkan ke siswa.
     */
    public function autoApplyTemplate(Student $student, User $teacher): ?StudentQuranTarget
    {
        $template = QuranTargetTemplate::where('school_id', $student->school_id)
            ->whereNull('grade_level')
            ->active()
            ->orderBy('created_at', 'desc')
            ->with('items')
            ->first();

        if (!$template || $template->items->isEmpty()) {
            return null;
        }

        $alreadyApplied = StudentQuranTarget::where('school_id', $student->school_id)
            ->where('student_id', $student->id)
            ->where('title', $template->title)
            ->exists();

        if ($alreadyApplied) {
            return null;
        }

        return $this->applyTemplateToStudent($student, $template, $teacher);
    }
}
