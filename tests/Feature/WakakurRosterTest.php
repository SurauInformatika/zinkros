<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassRoom;
use App\Models\ClassSubjectTeacher;
use App\Models\Subject;
use App\Models\User;
use Tests\TestCase;

class WakakurRosterTest extends TestCase
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

    public function test_roster_page_renders_class_without_mappings(): void
    {
        $schoolId = $this->schoolId();
        $suffix = now()->timestamp;
        $class = ClassRoom::create([
            'school_id' => $schoolId,
            'class_name' => 'RosterKosong ' . $suffix,
            'grade_level' => '7',
        ]);

        try {
            $this->actingAs(User::where('email', 'wakakur@sit.sch.id')->firstOrFail());
            $this->get('/wakasek/roster')
                ->assertOk()
                ->assertSee('Roster / Jadwal Kelas')
                ->assertSee('RosterKosong ' . $suffix, false)
                ->assertSee('Belum ada mapel diampu.');
        } finally {
            $class->delete();
        }
    }

    public function test_roster_shows_guru_and_missing_badges(): void
    {
        $schoolId = $this->schoolId();
        $suffix = now()->timestamp;
        $classComplete = ClassRoom::create(['school_id' => $schoolId, 'class_name' => 'RosterLengkap ' . $suffix, 'grade_level' => '7']);
        $classEmpty = ClassRoom::create(['school_id' => $schoolId, 'class_name' => 'RosterKurang ' . $suffix, 'grade_level' => '8']);
        $subject = Subject::create(['school_id' => $schoolId, 'name' => 'Mapel Roster ' . $suffix, 'type' => 'GENERAL']);

        try {
            $plot = ClassSubjectTeacher::create([
                'school_id' => $schoolId,
                'class_id' => $classComplete->id,
                'subject_id' => $subject->id,
                'teacher_id' => $this->guruId(),
            ]);

            $this->actingAs(User::where('email', 'wakakur@sit.sch.id')->firstOrFail());
            $this->get('/wakasek/roster')
                ->assertOk()
                ->assertSee('RosterLengkap ' . $suffix, false)
                ->assertSee('Mapel Roster ' . $suffix, false)
                ->assertSee('Ustadz Rizky', false)
                ->assertSee('RosterKurang ' . $suffix, false);
        } finally {
            if (isset($plot)) {
                $plot->delete();
            }
            $subject->delete();
            $classEmpty->delete();
            $classComplete->delete();
        }
    }

    public function test_roster_filters_by_academic_year_param(): void
    {
        $schoolId = $this->schoolId();
        $suffix = now()->timestamp;
        $ta1 = AcademicYear::create(['school_id' => $schoolId, 'name' => 'RosterTA1 ' . $suffix, 'start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'is_active' => false]);
        $ta2 = AcademicYear::create(['school_id' => $schoolId, 'name' => 'RosterTA2 ' . $suffix, 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => false]);
        $classA = ClassRoom::create(['school_id' => $schoolId, 'class_name' => 'RosterTAKlsA ' . $suffix, 'grade_level' => '7']);
        $classB = ClassRoom::create(['school_id' => $schoolId, 'class_name' => 'RosterTAKlsB ' . $suffix, 'grade_level' => '8']);
        $subA = Subject::create(['school_id' => $schoolId, 'name' => 'Mapel TA1 ' . $suffix, 'type' => 'GENERAL']);
        $subB = Subject::create(['school_id' => $schoolId, 'name' => 'Mapel TA2 ' . $suffix, 'type' => 'GENERAL']);

        try {
            $plotA = ClassSubjectTeacher::create(['school_id' => $schoolId, 'class_id' => $classA->id, 'subject_id' => $subA->id, 'teacher_id' => $this->guruId(), 'academic_year_id' => $ta1->id]);
            $plotB = ClassSubjectTeacher::create(['school_id' => $schoolId, 'class_id' => $classB->id, 'subject_id' => $subB->id, 'teacher_id' => $this->guruId(), 'academic_year_id' => $ta2->id]);

            $this->actingAs(User::where('email', 'wakakur@sit.sch.id')->firstOrFail());

            $this->get('/wakasek/roster?ta=' . $ta1->id)
                ->assertOk()
                ->assertSee('Mapel TA1 ' . $suffix, false)
                ->assertDontSee('Mapel TA2 ' . $suffix, false);

            $this->get('/wakasek/roster?ta=' . $ta2->id)
                ->assertOk()
                ->assertSee('Mapel TA2 ' . $suffix, false)
                ->assertDontSee('Mapel TA1 ' . $suffix, false);
        } finally {
            foreach ([$plotA ?? null, $plotB ?? null] as $plot) {
                $plot?->delete();
            }
            $subA->delete();
            $subB->delete();
            $classA->delete();
            $classB->delete();
            $ta1->delete();
            $ta2->delete();
        }
    }

    public function test_roster_search_filters_classes(): void
    {
        $schoolId = $this->schoolId();
        $suffix = now()->timestamp;
        $classFound = ClassRoom::create(['school_id' => $schoolId, 'class_name' => 'CariIni ' . $suffix, 'grade_level' => '7']);
        $classHidden = ClassRoom::create(['school_id' => $schoolId, 'class_name' => 'CariLain ' . $suffix, 'grade_level' => '8']);

        try {
            $this->actingAs(User::where('email', 'wakakur@sit.sch.id')->firstOrFail());
            $this->get('/wakasek/roster?q=CariIni')
                ->assertOk()
                ->assertSee('CariIni ' . $suffix, false)
                ->assertDontSee('CariLain ' . $suffix, false);
        } finally {
            $classFound->delete();
            $classHidden->delete();
        }
    }

    public function test_roster_accessible_by_kepsek(): void
    {
        $this->actingAs(User::where('email', 'kepsek@sit.sch.id')->firstOrFail());
        $this->get('/wakasek/roster')->assertOk();
    }

    public function test_roster_summary_counts_are_rendered(): void
    {
        $schoolId = $this->schoolId();
        $suffix = now()->timestamp;
        $class = ClassRoom::create(['school_id' => $schoolId, 'class_name' => 'RosterRingkas ' . $suffix, 'grade_level' => '7']);
        $subject = Subject::create(['school_id' => $schoolId, 'name' => 'Mapel Ringkas ' . $suffix, 'type' => 'GENERAL']);

        try {
            $plot = ClassSubjectTeacher::create([
                'school_id' => $schoolId,
                'class_id' => $class->id,
                'subject_id' => $subject->id,
                'teacher_id' => $this->guruId(),
            ]);

            $this->actingAs(User::where('email', 'wakakur@sit.sch.id')->firstOrFail());
            $this->get('/wakasek/roster')
                ->assertOk()
                ->assertSee('1 kelas · 1 mapel terpetakan', false);
        } finally {
            $plot?->delete();
            $subject->delete();
            $class->delete();
        }
    }
}