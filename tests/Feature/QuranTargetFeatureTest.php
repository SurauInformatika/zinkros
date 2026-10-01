<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\QuranMaster;
use App\Models\QuranTargetTemplate;
use App\Models\QuranTargetTemplateItem;
use App\Models\QuranTeachingAssignment;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentQuranTarget;
use App\Models\StudentQuranTargetItem;
use App\Models\TahfidzRecord;
use App\Models\User;
use App\Services\QuranTargetService;
use Tests\TestCase;

class QuranTargetFeatureTest extends TestCase
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

    private function makeTeacher(School $school): User
    {
        return User::create([
            'school_id' => $school->id,
            'name' => 'Guru Uji Target ' . now()->timestamp,
            'role' => 'guru',
            'email' => 'guru-target-' . now()->timestamp . '@test.id',
            'password' => bcrypt('password'),
        ]);
    }

    private function makeStudent(School $school, ClassRoom $class): Student
    {
        return Student::create([
            'school_id' => $school->id,
            'name' => 'Siswa Uji Target ' . now()->timestamp,
            'gender' => 'L',
            'nis' => 'NIS-' . now()->timestamp,
            'class_id' => $class->id,
        ]);
    }

    private function makeAssignment(School $school, Student $student, User $teacher, ?string $ayId = null): QuranTeachingAssignment
    {
        return QuranTeachingAssignment::create([
            'school_id' => $school->id,
            'academic_year_id' => $ayId,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
        ]);
    }

    public function test_service_sequential_progress_handles_overlap_and_gap(): void
    {
        $school = School::where('slug', 'sit-default')->firstOrFail();
        $class = ClassRoom::create(['school_id' => $school->id, 'class_name' => 'SeqKls ' . now()->timestamp, 'grade_level' => '7']);
        $student = $this->makeStudent($school, $class);
        $teacher = $this->makeTeacher($school);
        $surah = QuranMaster::firstOrFail();

        try {
            // Records: 1-10, then 8-15 (overlap), then 30-50 (gap at 16-29)
            $r1 = TahfidzRecord::create(['school_id' => $school->id, 'teacher_id' => $teacher->id, 'academic_year_id' => null, 'student_id' => $student->id, 'quran_master_id' => $surah->id, 'ayat_start' => 1, 'ayat_end' => 10, 'activity_type' => TahfidzRecord::TYPE_ZIADAH, 'score' => 90, 'recorded_date' => now()]);
            $r2 = TahfidzRecord::create(['school_id' => $school->id, 'teacher_id' => $teacher->id, 'academic_year_id' => null, 'student_id' => $student->id, 'quran_master_id' => $surah->id, 'ayat_start' => 8, 'ayat_end' => 15, 'activity_type' => TahfidzRecord::TYPE_ZIADAH, 'score' => 90, 'recorded_date' => now()]);
            $r3 = TahfidzRecord::create(['school_id' => $school->id, 'teacher_id' => $teacher->id, 'academic_year_id' => null, 'student_id' => $student->id, 'quran_master_id' => $surah->id, 'ayat_start' => 30, 'ayat_end' => 50, 'activity_type' => TahfidzRecord::TYPE_ZIADAH, 'score' => 90, 'recorded_date' => now()]);

            $service = app(QuranTargetService::class);

            // Target spans 1-10 -> fully covered (1-15 overlap, sequential up to 15, but capped to 10)
            $got1 = $service->sequentialAyatForSurah($student->id, $surah->id, 1, 10);
            $this->assertSame(10, $got1);

            // Target spans 1-20 -> stops at 15 (gap at 16 onwards)
            $got2 = $service->sequentialAyatForSurah($student->id, $surah->id, 1, 20);
            $this->assertSame(15, $got2);

            // Target spans 1-60 -> 1-15 covered, 16-29 gap, 30-50 cannot count after gap => 15
            $got3 = $service->sequentialAyatForSurah($student->id, $surah->id, 1, 60);
            $this->assertSame(15, $got3);
        } finally {
            TahfidzRecord::where('student_id', $student->id)->delete();
            $teacher->delete();
            $student->delete();
            $class->delete();
        }
    }

    public function test_service_ignores_murajaah_as_new_hafalan(): void
    {
        $school = School::where('slug', 'sit-default')->firstOrFail();
        $class = ClassRoom::create(['school_id' => $school->id, 'class_name' => 'MuraKls ' . now()->timestamp, 'grade_level' => '7']);
        $student = $this->makeStudent($school, $class);
        $teacher = $this->makeTeacher($school);
        $surah = QuranMaster::firstOrFail();

        try {
            // 1-20 ZIADAH then 1-20 MURAJAAH — murajaah should not extend progress
            TahfidzRecord::create(['school_id' => $school->id, 'teacher_id' => $teacher->id, 'academic_year_id' => null, 'student_id' => $student->id, 'quran_master_id' => $surah->id, 'ayat_start' => 1, 'ayat_end' => 20, 'activity_type' => TahfidzRecord::TYPE_ZIADAH, 'score' => 90, 'recorded_date' => now()]);
            TahfidzRecord::create(['school_id' => $school->id, 'teacher_id' => $teacher->id, 'academic_year_id' => null, 'student_id' => $student->id, 'quran_master_id' => $surah->id, 'ayat_start' => 1, 'ayat_end' => 25, 'activity_type' => TahfidzRecord::TYPE_MURAJAAH, 'score' => 90, 'recorded_date' => now()]);

            $service = app(QuranTargetService::class);
            $this->assertSame(20, $service->sequentialAyatForSurah($student->id, $surah->id, 1, 25));
        } finally {
            TahfidzRecord::where('student_id', $student->id)->delete();
            $teacher->delete();
            $student->delete();
            $class->delete();
        }
    }

    public function test_auto_apply_template_when_assignment_created(): void
    {
        $school = School::where('slug', 'sit-default')->firstOrFail();
        $class = ClassRoom::create(['school_id' => $school->id, 'class_name' => 'ApplyKls ' . now()->timestamp, 'grade_level' => '7']);
        $student = $this->makeStudent($school, $class);
        $teacher = $this->makeTeacher($school);

        $template = QuranTargetTemplate::create([
            'school_id' => $school->id,
            'grade_level' => null,
            'title' => 'Template Uji ' . now()->timestamp,
            'target_date' => now()->addMonths(6)->toDateString(),
            'is_active' => true,
        ]);
        $surahA = QuranMaster::where('surah_number', 78)->firstOrFail();
        $surahB = QuranMaster::where('surah_number', 79)->firstOrFail();
        QuranTargetTemplateItem::create(['template_id' => $template->id, 'quran_master_id' => $surahA->id, 'ayat_start' => 1, 'ayat_end' => $surahA->total_ayats]);
        QuranTargetTemplateItem::create(['template_id' => $template->id, 'quran_master_id' => $surahB->id, 'ayat_start' => 1, 'ayat_end' => $surahB->total_ayats]);

        $assignment = null;
        try {
            // Observer should create a target automatically
            $assignment = $this->makeAssignment($school, $student, $teacher);

            $target = StudentQuranTarget::where('student_id', $student->id)->first();
            $this->assertNotNull($target, 'Target auto-applied on assignment creation');
            $this->assertSame($template->title, $target->title);
            $this->assertSame(2, $target->items()->count());
            $this->assertTrue((bool) $target->is_active);
        } finally {
            if ($assignment) {
                StudentQuranTarget::where('student_id', $student->id)->delete();
                $assignment->delete();
            }
            $template->items()->delete();
            $template->delete();
            $teacher->delete();
            $student->delete();
            $class->delete();
        }
    }

    public function test_guru_can_create_update_and_delete_target_via_http(): void
    {
        $school = School::where('slug', 'sit-default')->firstOrFail();
        $class = ClassRoom::create(['school_id' => $school->id, 'class_name' => 'CrudKls ' . now()->timestamp, 'grade_level' => '7']);
        $student = $this->makeStudent($school, $class);
        $teacher = $this->makeTeacher($school);
        $assignment = null;
        $target = null;
        $title = 'Target CRUD ' . now()->timestamp;

        try {
            $assignment = $this->makeAssignment($school, $student, $teacher);
            $this->actingAs($teacher);
            $surah = QuranMaster::where('surah_number', 80)->firstOrFail();

            // CREATE
            $this->post(route('guru.quran-hafalan.target-store'), [
                'student_id' => $student->id,
                'title' => $title,
                'target_date' => now()->addMonths(3)->toDateString(),
                'surah_ids' => [$surah->id],
                'ayat_starts' => [1],
                'ayat_ends' => [10],
            ])->assertRedirect();

            $target = StudentQuranTarget::where('student_id', $student->id)->where('title', $title)->firstOrFail();
            $this->assertSame(1, $target->items()->count());
            $this->assertEquals(10, $target->items()->first()->ayat_end);

            // VIEW create page
            $this->get(route('guru.quran-hafalan.target-create', $student->id))->assertOk();

            // UPDATE
            $this->put(route('guru.quran-hafalan.target-update', [$student->id, $target->id]), [
                'title' => 'Target CRUD Updated',
                'target_date' => now()->addMonths(4)->toDateString(),
                'is_active' => 1,
                'surah_ids' => [$surah->id],
                'ayat_starts' => [1],
                'ayat_ends' => [25],
            ])->assertRedirect();

            $target->refresh();
            $this->assertSame('Target CRUD Updated', $target->title);
            $this->assertEquals(25, $target->items()->first()->ayat_end);

            // DELETE
            $this->delete(route('guru.quran-hafalan.target-destroy', [$student->id, $target->id]))->assertRedirect();
            $this->assertNull(StudentQuranTarget::find($target->id));
            $target = null;
        } finally {
            if ($target) {
                $target->items()->delete();
                $target->delete();
            }
            StudentQuranTarget::where('student_id', $student->id)->each(function ($t) {
                $t->items()->delete();
                $t->delete();
            });
            $assignment?->delete();
            $teacher->delete();
            $student->delete();
            $class->delete();
        }
    }

    public function test_unauthorized_teacher_cannot_access_student(): void
    {
        $school = School::where('slug', 'sit-default')->firstOrFail();
        $class = ClassRoom::create(['school_id' => $school->id, 'class_name' => 'AuthKls ' . now()->timestamp, 'grade_level' => '7']);
        $student = $this->makeStudent($school, $class);
        $otherTeacher = $this->makeTeacher($school);

        try {
            $this->actingAs($otherTeacher);
            $this->get(route('guru.quran-hafalan.target-create', $student->id))
                ->assertForbidden();
        } finally {
            $otherTeacher->delete();
            $student->delete();
            $class->delete();
        }
    }

    public function test_murid_hafalan_page_renders_for_linked_student(): void
    {
        $school = School::where('slug', 'sit-default')->firstOrFail();
        $class = ClassRoom::create(['school_id' => $school->id, 'class_name' => 'MuriKls ' . now()->timestamp, 'grade_level' => '7']);
        $student = $this->makeStudent($school, $class);
        $murid = User::create([
            'school_id' => $school->id,
            'name' => 'Murid Uji ' . now()->timestamp,
            'role' => 'murid',
            'email' => 'murid-' . now()->timestamp . '@test.id',
            'password' => bcrypt('password'),
            'student_id' => $student->id,
        ]);
        $teacher = $this->makeTeacher($school);
        $assignment = null;
        $target = null;

        try {
            $assignment = $this->makeAssignment($school, $student, $teacher);
            $surah = QuranMaster::where('surah_number', 90)->firstOrFail();
            $target = StudentQuranTarget::create([
                'school_id' => $school->id,
                'student_id' => $student->id,
                'teacher_id' => $teacher->id,
                'title' => 'Target Murid ' . now()->timestamp,
                'target_date' => now()->addMonths(6)->toDateString(),
                'is_active' => true,
            ]);
            StudentQuranTargetItem::create(['target_id' => $target->id, 'quran_master_id' => $surah->id, 'ayat_start' => 1, 'ayat_end' => '10']);

            $this->actingAs($murid);
            $this->get(route('murid.hafalan'))->assertOk()->assertSee('Target Hafalan Saya', false);
            $this->getJson(route('murid.hafalan.chart') . '?filter=ta_init')->assertOk();
        } finally {
            if ($target) {
                $target->items()->delete();
                $target->delete();
            }
            $assignment?->delete();
            $murid->delete();
            $teacher->delete();
            $student->delete();
            $class->delete();
        }
    }
}
