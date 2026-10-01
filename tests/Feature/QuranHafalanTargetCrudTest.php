<?php

namespace Tests\Feature;

use App\Models\QuranMaster;
use App\Models\StudentQuranTarget;
use App\Models\StudentQuranTargetItem;
use App\Models\User;
use Tests\TestCase;

class QuranHafalanTargetCrudTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'mysql']);
        config(['database.connections.mysql.host' => '127.0.0.1']);
        config(['database.connections.mysql.port' => '3306']);
        config(['database.connections.mysql.database' => 'sit_school']);
        config(['database.connections.mysql.username' => 'root']);
        config(['database.connections.mysql.password' => '']);
    }

    public function test_target_edit_update_and_destroy_flow(): void
    {
        $teacher = User::where('email', 'ana@mail.com')->firstOrFail();
        $studentId = '01a043fa-8e26-7108-8c47-6f8c4cb23567';
        $surah = QuranMaster::first();

        $this->actingAs($teacher);

        $target = StudentQuranTarget::create([
            'school_id' => $teacher->school_id,
            'academic_year_id' => null,
            'student_id' => $studentId,
            'teacher_id' => $teacher->id,
            'title' => 'Target Uji CRUD',
            'target_date' => now()->addMonths(3)->toDateString(),
            'is_active' => true,
        ]);

        StudentQuranTargetItem::create([
            'target_id' => $target->id,
            'quran_master_id' => $surah->id,
            'ayat_start' => 1,
            'ayat_end' => 10,
        ]);

        try {
            $editUrl = route('guru.quran-hafalan.target-edit', [$studentId, $target->id]);
            $destroyUrl = route('guru.quran-hafalan.target-destroy', [$studentId, $target->id]);

            $inputResp = $this->get(route('guru.quran-hafalan.input', $studentId));
            $inputResp->assertOk();
            $this->assertStringContainsString('Target Uji CRUD', $inputResp->getContent(), 'input page contains title');
            $this->assertStringContainsString($editUrl, $inputResp->getContent(), 'input page has edit link');
            $this->assertStringContainsString($destroyUrl, $inputResp->getContent(), 'input page has delete form');
            $this->assertStringContainsString('title="Edit target"', $inputResp->getContent(), 'input page edit icon');
            $this->assertStringContainsString('title="Hapus target"', $inputResp->getContent(), 'input page delete icon');

            $historyResp = $this->get(route('guru.quran-hafalan.history', $studentId));
            $historyResp->assertOk();
            $this->assertStringContainsString('Target Uji CRUD', $historyResp->getContent(), 'history page contains title');
            $this->assertStringContainsString($editUrl, $historyResp->getContent(), 'history page has edit link');
            $this->assertStringContainsString($destroyUrl, $historyResp->getContent(), 'history page has delete form');

            $this->get(route('guru.quran-hafalan.target-edit', [$studentId, $target->id]))
                ->assertOk()
                ->assertSee('Edit Target Hafalan')
                ->assertSee('Target Uji CRUD');

            $this->put(route('guru.quran-hafalan.target-update', [$studentId, $target->id]), [
                'title' => 'Target Uji CRUD (diubah)',
                'target_date' => now()->addMonths(4)->toDateString(),
                'notes' => 'Catatan uji',
                'is_active' => '1',
                'surah_ids' => [$surah->id],
                'ayat_starts' => [1],
                'ayat_ends' => [5],
            ])->assertRedirect(route('guru.quran-hafalan.input', $studentId));

            $target->refresh();
            $this->assertSame('Target Uji CRUD (diubah)', $target->title);
            $this->assertSame(1, $target->items()->count());
            $this->assertSame(5, (int) $target->items()->first()->ayat_end);

            $this->delete(route('guru.quran-hafalan.target-destroy', [$studentId, $target->id]))
                ->assertRedirect(route('guru.quran-hafalan.input', $studentId));

            $this->assertDatabaseMissing('student_quran_targets', ['id' => $target->id]);
        } finally {
            if (StudentQuranTarget::find($target->id)) {
                $target->items()->delete();
                $target->delete();
            }
        }
    }
}