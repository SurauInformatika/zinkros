<?php

namespace Tests\Feature;

use App\Models\AcademicCalendar;
use App\Models\AcademicYear;
use App\Models\CalendarLevelStructure;
use App\Models\ClassRoom;
use App\Models\ClassSubjectTeacher;
use App\Models\Notification;
use App\Models\Subject;
use App\Models\User;
use Tests\TestCase;

class WakakurKaldikRevisionTest extends TestCase
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

        $sid = $this->schoolId();
        foreach (AcademicYear::where('school_id', $sid)->where('name', 'like', 'RevTA %')->get() as $y) {
            $cals = AcademicCalendar::where('school_id', $sid)->where('academic_year_id', $y->id)->get();
            foreach ($cals as $c) {
                CalendarLevelStructure::where('academic_calendar_id', $c->id)->delete();
                $c->delete();
            }
            $y->delete();
        }
        ClassRoom::where('school_id', $sid)->where('class_name', 'like', 'RevKls %')->delete();
        Subject::where('school_id', $sid)->where('name', 'like', 'Mapel Rev %')->delete();
    }

    private function wakakur(): User
    {
        return User::where('email', 'wakakur@sit.sch.id')->firstOrFail();
    }

    private function kepsek(): User
    {
        return User::where('email', 'kepsek@sit.sch.id')->firstOrFail();
    }

    private function schoolId(): string
    {
        return $this->wakakur()->school_id;
    }

    private function makeCalendar(string $status = 'final', int $version = 1, ?string $parentId = null): array
    {
        $suffix = now()->timestamp . '_' . bin2hex(random_bytes(3));
        $year = AcademicYear::create([
            'school_id' => $this->schoolId(),
            'name' => 'RevTA ' . $suffix,
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => false,
        ]);
        $calendar = AcademicCalendar::create([
            'school_id' => $this->schoolId(),
            'academic_year_id' => $year->id,
            'name' => 'KaldikRev ' . $suffix,
            'semester' => 1,
            'source' => 'custom',
            'start_date' => '2026-07-13',
            'end_date' => '2026-12-19',
            'status' => $status,
            'version' => $version,
            'parent_version_id' => $parentId,
            'created_by' => $this->wakakur()->id,
        ]);

        return [$calendar, $year];
    }

    private function seedStructures(AcademicCalendar $calendar, array $jp): void
    {
        CalendarLevelStructure::create([
            'academic_calendar_id' => $calendar->id,
            'grade_level_start' => 7,
            'grade_level_end' => 9,
            'jp_duration_minutes' => 35,
            'jp_per_day' => $jp,
        ]);
    }

    public function test_wakakur_cannot_revise_draft_or_pending(): void
    {
        [$calendar, $year] = $this->makeCalendar('pending', 1);

        try {
            app(\App\Services\SchoolContext::class)->set(null);
            $this->actingAs($this->wakakur());

            $this->post(route('wakasek.kaldik.revise', $calendar))
                ->assertSessionHas('error');
            $this->assertSame(0, AcademicCalendar::where('parent_version_id', $calendar->id)->count());
        } finally {
            $calendar->delete();
            $year->delete();
        }
    }

    public function test_revise_requires_same_school(): void
    {
        [$calendar, $year] = $this->makeCalendar('final', 1);

        try {
            app(\App\Services\SchoolContext::class)->set(null);
            $other = User::where('email', 'wakakur@dafi.com')->firstOrFail();
            $this->actingAs($other);

            $this->post(route('wakasek.kaldik.revise', $calendar))->assertForbidden();
        } finally {
            $calendar->delete();
            $year->delete();
        }
    }

    public function test_revise_final_creates_draft_revision_with_copied_structures(): void
    {
        [$calendar, $year] = $this->makeCalendar('final', 1);
        $this->seedStructures($calendar, ['senin' => 7, 'jumat' => 0, 'ahad' => 0]);

        try {
            app(\App\Services\SchoolContext::class)->set(null);
            $this->actingAs($this->wakakur());

            $this->post(route('wakasek.kaldik.revise', $calendar))
                ->assertRedirect()
                ->assertSessionHas('success');

            $revision = AcademicCalendar::where('parent_version_id', $calendar->id)->first();
            $this->assertNotNull($revision);
            $this->assertSame('draft', $revision->status);
            $this->assertSame(2, (int) $revision->version);
            $this->assertSame($calendar->id, $revision->parent_version_id);
            $revLevel = CalendarLevelStructure::where('academic_calendar_id', $revision->id)->firstOrFail();
            $this->assertSame(7, $revLevel->jpForDay('senin'));
            $this->assertSame(1, CalendarLevelStructure::where('academic_calendar_id', $revision->id)->count());

            $calendars = AcademicCalendar::find([$calendar->id, $revision->id]);
            $this->assertTrue($calendars->firstWhere('id', $calendar->id)->isFinal());
        } finally {
            $calendar->delete();
            $year->delete();
        }
    }

    public function test_second_revision_blocked_while_revision_in_flight(): void
    {
        [$calendar, $year] = $this->makeCalendar('final', 1);
        $this->seedStructures($calendar, ['senin' => 5, 'jumat' => 0, 'ahad' => 0]);

        try {
            app(\App\Services\SchoolContext::class)->set(null);
            $this->actingAs($this->wakakur());

            $this->post(route('wakasek.kaldik.revise', $calendar))->assertSessionHas('success');

            $revision = AcademicCalendar::where('parent_version_id', $calendar->id)->firstOrFail();
            $this->post(route('wakasek.kaldik.revise', $calendar))->assertSessionHas('error');

            $this->assertSame(1, AcademicCalendar::where('parent_version_id', $calendar->id)->count());
            $this->assertSame('draft', $revision->fresh()->status);
        } finally {
            $calendar->delete();
            $year->delete();
        }
    }

    public function test_approve_revision_archives_parent_and_roster_uses_new_final(): void
    {
        [$calendar, $year] = $this->makeCalendar('final', 1);
        $this->seedStructures($calendar, ['senin' => 5, 'jumat' => 0, 'ahad' => 0]);

        $class = ClassRoom::create(['school_id' => $this->schoolId(), 'class_name' => 'RevKls ' . now()->timestamp, 'grade_level' => '7']);

        try {
            app(\App\Services\SchoolContext::class)->set(null);
            $this->actingAs($this->wakakur());

            $this->post(route('wakasek.kaldik.revise', $calendar))->assertSessionHas('success');
            $revision = AcademicCalendar::where('parent_version_id', $calendar->id)->firstOrFail();
            $this->post(route('wakasek.kaldik.submit', $revision))->assertSessionHas('success');
            $this->assertSame('pending', $revision->fresh()->status);

            $this->actingAs($this->kepsek());
            $this->post(route('kepsek.kaldik.approve', $revision))->assertSessionHas('success');

            $this->assertSame('final', $revision->fresh()->status);
            $this->assertSame('archived', $calendar->fresh()->status);

            $notif = Notification::where('type', Notification::TYPE_KALDIK_APPROVED)->latest('created_at')->first();
            $this->assertNotNull($notif);
        } finally {
            $revisionId = $revision->id ?? null;
            CalendarLevelStructure::where('academic_calendar_id', $calendar->id)
                ->when($revisionId, fn ($q) => $q->orWhere('academic_calendar_id', $revisionId))
                ->delete();
            $revision?->delete();
            $class->delete();
            $calendar->delete();
            $year->delete();
        }
    }

    public function test_revising_an_existing_revision_creates_next_version(): void
    {
        [$calendar, $year] = $this->makeCalendar('final', 1);
        $revision = AcademicCalendar::create([
            'school_id' => $this->schoolId(),
            'academic_year_id' => $year->id,
            'name' => $calendar->name . ' v2',
            'semester' => 1,
            'source' => 'custom',
            'start_date' => '2026-07-13',
            'end_date' => '2026-12-19',
            'status' => 'final',
            'version' => 2,
            'parent_version_id' => $calendar->id,
            'created_by' => $this->wakakur()->id,
        ]);
        $this->seedStructures($revision, ['senin' => 5, 'jumat' => 0, 'ahad' => 0]);

        try {
            app(\App\Services\SchoolContext::class)->set(null);
            $this->actingAs($this->wakakur());

            $this->post(route('wakasek.kaldik.revise', $revision))
                ->assertSessionHas('success');

            $v3 = AcademicCalendar::where('parent_version_id', $revision->id)->first();
            $this->assertNotNull($v3);
            $this->assertSame(3, (int) $v3->version);
            $this->assertSame('draft', $v3->status);
        } finally {
            $revision->delete();
            $calendar->delete();
            $year->delete();
        }
    }

    public function test_jpPerDay_uses_active_final_and_ignores_draft(): void
    {
        $suffix = now()->timestamp;
        $schoolId = $this->schoolId();

        $year = AcademicYear::create([
            'school_id' => $schoolId,
            'name' => 'RevTA ' . $suffix,
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);
        $final = AcademicCalendar::create([
            'school_id' => $schoolId,
            'academic_year_id' => $year->id,
            'name' => 'KaldikFinal ' . $suffix,
            'semester' => 1,
            'source' => 'custom',
            'start_date' => '2026-07-13',
            'end_date' => '2026-12-19',
            'status' => 'final',
            'version' => 1,
            'created_by' => $this->wakakur()->id,
        ]);
        $draft = AcademicCalendar::create([
            'school_id' => $schoolId,
            'academic_year_id' => $year->id,
            'name' => 'KaldikDraft ' . $suffix,
            'semester' => 1,
            'source' => 'custom',
            'start_date' => '2026-07-13',
            'end_date' => '2026-12-19',
            'status' => 'draft',
            'version' => 2,
            'created_by' => $this->wakakur()->id,
        ]);
        $this->seedStructures($final, ['senin' => 5, 'jumat' => 0, 'ahad' => 0]);
        $this->seedStructures($draft, ['senin' => 31337, 'jumat' => 0, 'ahad' => 0]);

        $class = ClassRoom::create(['school_id' => $schoolId, 'class_name' => 'RevKls ' . $suffix, 'grade_level' => '7']);
        $subject = Subject::create(['school_id' => $schoolId, 'name' => 'Mapel Rev ' . $suffix, 'type' => 'GENERAL']);
        $plot = ClassSubjectTeacher::create([
            'school_id' => $schoolId,
            'class_id' => $class->id,
            'subject_id' => $subject->id,
            'teacher_id' => User::where('email', 'guru@sit.sch.id')->firstOrFail()->id,
            'academic_year_id' => $year->id,
        ]);

        try {
            $this->actingAs($this->wakakur());

            $this->get(route('wakasek.base.roster.jadwal-edit', $class))
                ->assertOk()
                ->assertDontSee('31337')
                ->assertSee('5 JP');
        } finally {
            CalendarLevelStructure::where('academic_calendar_id', $final->id)->delete();
            CalendarLevelStructure::where('academic_calendar_id', $draft->id)->delete();
            $plot->delete();
            $subject->delete();
            $class->delete();
            $final->delete();
            $draft->delete();
            $year->delete();
        }
    }
}