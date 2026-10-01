<?php

namespace Tests\Feature;

use App\Models\ClassDaySchedule;
use App\Models\ClassRoom;
use App\Models\ClassSubjectTeacher;
use App\Models\School;
use App\Models\Subject;
use App\Models\User;
use Tests\TestCase;

class WakakurJadwalTest extends TestCase
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

    private function schoolId(): string
    {
        return User::where('email', 'wakakur@sit.sch.id')->firstOrFail()->school_id;
    }

    private function guruId(): string
    {
        return User::where('email', 'guru@sit.sch.id')->where('school_id', $this->schoolId())->firstOrFail()->id;
    }

    public function test_editor_page_renders_with_subject_options(): void
    {
        $schoolId = $this->schoolId();
        $suffix = now()->timestamp;
        $class = ClassRoom::create(['school_id' => $schoolId, 'class_name' => 'JadwalKls ' . $suffix, 'grade_level' => '7']);
        $subject = Subject::create(['school_id' => $schoolId, 'name' => 'Mapel Editor ' . $suffix, 'type' => 'GENERAL']);

        try {
            $plot = ClassSubjectTeacher::create([
                'school_id' => $schoolId,
                'class_id' => $class->id,
                'subject_id' => $subject->id,
                'teacher_id' => $this->guruId(),
            ]);

            $this->actingAs(User::where('email', 'wakakur@sit.sch.id')->firstOrFail());
            $this->get(route('wakasek.base.roster.jadwal-edit', $class))
                ->assertOk()
                ->assertSee('Jadwal Mingguan · ' . $class->class_name, false)
                ->assertSee('Mapel Editor ' . $suffix, false)
                ->assertSee('Ustadz Rizky', false)
                ->assertSee('Jam 1', false);
        } finally {
            $plot?->delete();
            $subject->delete();
            $class->delete();
        }
    }

    public function test_editor_saves_blocks_and_roster_grid_shows_them(): void
    {
        $schoolId = $this->schoolId();
        $suffix = now()->timestamp;
        $class = ClassRoom::create(['school_id' => $schoolId, 'class_name' => 'JadwalSimpan ' . $suffix, 'grade_level' => '7']);
        $subject = Subject::create(['school_id' => $schoolId, 'name' => 'Mapel Simpan ' . $suffix, 'type' => 'GENERAL']);

        try {
            $plot = ClassSubjectTeacher::create([
                'school_id' => $schoolId,
                'class_id' => $class->id,
                'subject_id' => $subject->id,
                'teacher_id' => $this->guruId(),
            ]);

            $this->actingAs(User::where('email', 'wakakur@sit.sch.id')->firstOrFail());
            $this->post(route('wakasek.base.roster.jadwal-store', $class), [
                'schedules' => [
                    'senin' => ['1' => $subject->id, '2' => $subject->id],
                    'rabu' => ['3' => $subject->id],
                ],
            ])->assertRedirect(route('wakasek.base.roster.jadwal-edit', $class))
                ->assertSessionHas('status');

            $blocks = ClassDaySchedule::where('class_id', $class->id)->get();
            $this->assertCount(2, $blocks);
            $this->assertSame('senin', $blocks->first()->day_name);
            $this->assertSame(1, (int) $blocks->first()->start_jp);
            $this->assertSame(2, (int) $blocks->first()->end_jp);

            $this->get(route('wakasek.base.roster', ['view' => 'jadwal']))
                ->assertOk()
                ->assertSee('JadwalSimpan ' . $suffix, false)
                ->assertSee('Mapel Simpan ' . $suffix, false)
                ->assertSee('Sudah diatur', false);
        } finally {
            ClassDaySchedule::where('class_id', $class->id)->delete();
            $plot?->delete();
            $subject->delete();
            $class->delete();
        }
    }

    public function test_empty_submit_clears_existing_schedule(): void
    {
        $schoolId = $this->schoolId();
        $suffix = now()->timestamp;
        $class = ClassRoom::create(['school_id' => $schoolId, 'class_name' => 'JadwalKosong ' . $suffix, 'grade_level' => '7']);
        $subject = Subject::create(['school_id' => $schoolId, 'name' => 'Mapel Kosong ' . $suffix, 'type' => 'GENERAL']);

        try {
            $plot = ClassSubjectTeacher::create([
                'school_id' => $schoolId,
                'class_id' => $class->id,
                'subject_id' => $subject->id,
                'teacher_id' => $this->guruId(),
            ]);
            ClassDaySchedule::create([
                'school_id' => $schoolId,
                'class_id' => $class->id,
                'subject_id' => $subject->id,
                'teacher_id' => $this->guruId(),
                'day_name' => 'senin',
                'start_jp' => 1,
                'end_jp' => 2,
            ]);

            $this->actingAs(User::where('email', 'wakakur@sit.sch.id')->firstOrFail());
            $this->post(route('wakasek.base.roster.jadwal-store', $class), ['schedules' => []])
                ->assertRedirect(route('wakasek.base.roster.jadwal-edit', $class))
                ->assertSessionHas('status');

            $this->assertSame(0, ClassDaySchedule::where('class_id', $class->id)->count());
        } finally {
            ClassDaySchedule::where('class_id', $class->id)->delete();
            $plot?->delete();
            $subject->delete();
            $class->delete();
        }
    }

    public function test_editor_rejects_cross_class_teacher_conflict(): void
    {
        $schoolId = $this->schoolId();
        $suffix = now()->timestamp;
        $classA = ClassRoom::create(['school_id' => $schoolId, 'class_name' => 'JadwalKlsA ' . $suffix, 'grade_level' => '7']);
        $classB = ClassRoom::create(['school_id' => $schoolId, 'class_name' => 'JadwalKlsB ' . $suffix, 'grade_level' => '8']);
        $subA = Subject::create(['school_id' => $schoolId, 'name' => 'Mapel Bento A ' . $suffix, 'type' => 'GENERAL']);
        $subB = Subject::create(['school_id' => $schoolId, 'name' => 'Mapel Bento B ' . $suffix, 'type' => 'GENERAL']);

        try {
            $plotA = ClassSubjectTeacher::create(['school_id' => $schoolId, 'class_id' => $classA->id, 'subject_id' => $subA->id, 'teacher_id' => $this->guruId()]);
            $plotB = ClassSubjectTeacher::create(['school_id' => $schoolId, 'class_id' => $classB->id, 'subject_id' => $subB->id, 'teacher_id' => $this->guruId()]);
            ClassDaySchedule::create([
                'school_id' => $schoolId,
                'class_id' => $classB->id,
                'subject_id' => $subB->id,
                'teacher_id' => $this->guruId(),
                'day_name' => 'senin',
                'start_jp' => 2,
                'end_jp' => 2,
            ]);

            $this->actingAs(User::where('email', 'wakakur@sit.sch.id')->firstOrFail());
            $this->post(route('wakasek.base.roster.jadwal-store', $classA), [
                'schedules' => ['senin' => ['2' => $subA->id]],
            ])->assertSessionHas('error');

            $this->assertSame(0, ClassDaySchedule::where('class_id', $classA->id)->count());
        } finally {
            ClassDaySchedule::whereIn('class_id', [$classA->id, $classB->id])->delete();
            $plotA?->delete();
            $plotB?->delete();
            $subA->delete();
            $subB->delete();
            $classA->delete();
            $classB->delete();
        }
    }

    public function test_foreign_class_is_not_accessible(): void
    {
        $schoolId = $this->schoolId();
        $foreignSchool = School::query()->where('id', '!=', $schoolId)->firstOrFail();
        $suffix = now()->timestamp;
        $class = ClassRoom::create(['school_id' => $foreignSchool->id, 'class_name' => 'JadwalAsing ' . $suffix, 'grade_level' => '7']);

        try {
            app(\App\Services\SchoolContext::class)->set(null);
            $this->actingAs(User::where('email', 'wakakur@sit.sch.id')->firstOrFail());

            $get = $this->get(route('wakasek.base.roster.jadwal-edit', $class));
            $post = $this->post(route('wakasek.base.roster.jadwal-store', $class), ['schedules' => []]);

            $this->assertTrue(in_array($get->getStatusCode(), [403, 404], true), 'GET kelas asing harus ditolak, dapat ' . $get->getStatusCode());
            $this->assertTrue(in_array($post->getStatusCode(), [403, 404], true), 'POST kelas asing harus ditolak, dapat ' . $post->getStatusCode());
        } finally {
            $class->delete();
        }
    }

    public function test_roster_jadwal_view_shows_empty_state_per_class(): void
    {
        $schoolId = $this->schoolId();
        $suffix = now()->timestamp;
        $class = ClassRoom::create(['school_id' => $schoolId, 'class_name' => 'JadwalKosong2 ' . $suffix, 'grade_level' => '7']);

        try {
            $this->actingAs(User::where('email', 'wakakur@sit.sch.id')->firstOrFail());
            $this->get(route('wakasek.base.roster', ['view' => 'jadwal']))
                ->assertOk()
                ->assertSee('JadwalKosong2 ' . $suffix, false)
                ->assertSee('Belum diatur', false);
        } finally {
            $class->delete();
        }
    }

    public function test_ahad_schedule_storage_and_grid(): void
    {
        $schoolId = $this->schoolId();
        $suffix = now()->timestamp;
        $class = ClassRoom::create(['school_id' => $schoolId, 'class_name' => 'JadwalAhad ' . $suffix, 'grade_level' => '7']);
        $subject = Subject::create(['school_id' => $schoolId, 'name' => 'Mapel Ahad ' . $suffix, 'type' => 'GENERAL']);

        try {
            $plot = ClassSubjectTeacher::create([
                'school_id' => $schoolId,
                'class_id' => $class->id,
                'subject_id' => $subject->id,
                'teacher_id' => $this->guruId(),
            ]);

            $this->actingAs(User::where('email', 'wakakur@sit.sch.id')->firstOrFail());

            $this->post(route('wakasek.base.roster.jadwal-store', $class), [
                'schedules' => ['ahad' => ['1' => $subject->id, '2' => $subject->id]],
            ])->assertRedirect(route('wakasek.base.roster.jadwal-edit', $class))
                ->assertSessionHas('status');

            $block = ClassDaySchedule::where('class_id', $class->id)->where('day_name', 'ahad')->first();
            $this->assertNotNull($block);
            $this->assertSame(1, (int) $block->start_jp);
            $this->assertSame(2, (int) $block->end_jp);

            $this->get(route('wakasek.base.roster', ['view' => 'jadwal']))
                ->assertOk()
                ->assertSee('JadwalAhad ' . $suffix, false)
                ->assertSee('Ahad', false)
                ->assertSee('Mapel Ahad ' . $suffix, false);
        } finally {
            ClassDaySchedule::where('class_id', $class->id)->delete();
            $plot?->delete();
            $subject->delete();
            $class->delete();
        }
    }

    public function test_kepsek_can_open_editor(): void
    {
        $schoolId = $this->schoolId();
        $suffix = now()->timestamp;
        $class = ClassRoom::create(['school_id' => $schoolId, 'class_name' => 'JadwalKepsek ' . $suffix, 'grade_level' => '7']);

        try {
            $this->actingAs(User::where('email', 'kepsek@sit.sch.id')->firstOrFail());
            $this->get(route('wakasek.base.roster.jadwal-edit', $class))->assertOk();
        } finally {
            $class->delete();
        }
    }
}