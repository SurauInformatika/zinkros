<?php

namespace Tests\Feature;

use App\Models\AcademicCalendar;
use App\Models\AcademicYear;
use App\Models\CalendarClassOverride;
use App\Models\CalendarHoliday;
use App\Models\CalendarLevelStructure;
use App\Models\User;
use Tests\TestCase;

class WakakurKaldikDayStructureTest extends TestCase
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

    private function structures(array $jpPerDay): array
    {
        return [[
            'grade_level_start' => 1,
            'grade_level_end' => 6,
            'jp_duration_minutes' => 35,
            'jp_per_day' => $jpPerDay,
        ]];
    }

    public function test_store_creates_annual_calendar_without_semester(): void
    {
        $schoolId = $this->schoolId();
        $suffix = now()->timestamp;
        $year = AcademicYear::create(['school_id' => $schoolId, 'name' => 'TAAnnual ' . $suffix, 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => false]);
        $cal = null;

        try {
            app(\App\Services\SchoolContext::class)->set(null);
            $this->actingAs(User::where('email', 'wakakur@sit.sch.id')->firstOrFail());

            $this->post(route('wakasek.kaldik.store'), [
                'academic_year_id' => $year->id,
                'name' => 'KALDIK ' . $suffix,
                'source' => 'custom',
                'start_date' => '2026-07-13',
                'end_date' => '2027-06-30',
            ])->assertRedirect();

            $cal = AcademicCalendar::where('school_id', $schoolId)->where('academic_year_id', $year->id)->where('name', 'KALDIK ' . $suffix)->firstOrFail();
            $this->assertSame(0, (int) $cal->semester);
            $this->assertTrue($cal->isAnnual());
        } finally {
            $cal?->delete();
            $year->delete();
        }
    }

    public function test_store_twice_for_same_academic_year_bumps_version(): void
    {
        $schoolId = $this->schoolId();
        $suffix = now()->timestamp;
        $year = AcademicYear::create(['school_id' => $schoolId, 'name' => 'TADup ' . $suffix, 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => false]);
        $wakakur = User::where('email', 'wakakur@sit.sch.id')->firstOrFail();
        $legacy = null;
        $c1 = null;
        $c2 = null;

        try {
            $legacy = AcademicCalendar::create([
                'school_id' => $schoolId,
                'academic_year_id' => $year->id,
                'name' => 'Legacy v1 sem 1',
                'semester' => 1,
                'source' => 'custom',
                'start_date' => '2026-07-13',
                'end_date' => '2026-12-31',
                'status' => 'final',
                'version' => 1,
                'created_by' => $wakakur->id,
            ]);

            app(\App\Services\SchoolContext::class)->set(null);
            $this->actingAs($wakakur);

            $payload = ['academic_year_id' => $year->id, 'name' => 'Dup ' . $suffix, 'source' => 'custom', 'start_date' => '2026-07-13', 'end_date' => '2027-06-30'];

            $this->post(route('wakasek.kaldik.store'), $payload)->assertRedirect();
            $this->post(route('wakasek.kaldik.store'), $payload)->assertRedirect();

            $c1 = AcademicCalendar::where('school_id', $schoolId)->where('academic_year_id', $year->id)->where('version', 2)->firstOrFail();
            $c2 = AcademicCalendar::where('school_id', $schoolId)->where('academic_year_id', $year->id)->where('version', 3)->firstOrFail();
            $this->assertSame(0, (int) $c1->semester);
            $this->assertSame(0, (int) $c2->semester);
        } finally {
            $c1?->delete();
            $c2?->delete();
            $legacy?->delete();
            $year->delete();
        }
    }

    public function test_level_structure_save_accepts_ahad(): void
    {
        $schoolId = $this->schoolId();
        $suffix = now()->timestamp;
        $year = AcademicYear::create(['school_id' => $schoolId, 'name' => 'KaldikTA ' . $suffix, 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => false]);
        $calendar = AcademicCalendar::create([
            'school_id' => $schoolId,
            'academic_year_id' => $year->id,
            'name' => 'KaldikTest ' . $suffix,
            'semester' => 1,
            'source' => 'custom',
            'start_date' => '2026-07-13',
            'end_date' => '2026-12-19',
            'status' => 'draft',
            'version' => 1,
            'created_by' => User::where('email', 'wakakur@sit.sch.id')->firstOrFail()->id,
        ]);

        try {
            app(\App\Services\SchoolContext::class)->set(null);
            $this->actingAs(User::where('email', 'wakakur@sit.sch.id')->firstOrFail());

            $jp = ['senin' => 7, 'selasa' => 7, 'rabu' => 7, 'kamis' => 7, 'jumat' => 5, 'sabtu' => 0, 'ahad' => 0];

            $this->post(route('wakasek.kaldik.level-structure', $calendar), ['structures' => $this->structures($jp)])
                ->assertSessionHas('success');

            $this->assertSame(1, CalendarLevelStructure::where('academic_calendar_id', $calendar->id)->count());
            $row = CalendarLevelStructure::where('academic_calendar_id', $calendar->id)->firstOrFail();
            $this->assertSame(7, $row->jpForDay('senin'));
            $this->assertSame(0, $row->jpForDay('ahad'));

            $this->get(route('wakasek.kaldik.edit', $calendar))->assertOk()->assertSee('ahad', false);
        } finally {
            $calendar->delete();
            $year->delete();
        }
    }

    public function test_school_can_swap_ahad_as_learning_day(): void
    {
        $schoolId = $this->schoolId();
        $suffix = now()->timestamp;
        $year = AcademicYear::create(['school_id' => $schoolId, 'name' => 'KaldikTA ' . $suffix, 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => false]);
        $calendar = AcademicCalendar::create([
            'school_id' => $schoolId,
            'academic_year_id' => $year->id,
            'name' => 'KaldikTest ' . $suffix,
            'semester' => 1,
            'source' => 'custom',
            'start_date' => '2026-07-13',
            'end_date' => '2026-12-19',
            'status' => 'draft',
            'version' => 1,
            'created_by' => User::where('email', 'wakakur@sit.sch.id')->firstOrFail()->id,
        ]);

        try {
            app(\App\Services\SchoolContext::class)->set(null);
            $this->actingAs(User::where('email', 'wakakur@sit.sch.id')->firstOrFail());

            $jp = ['senin' => 7, 'selasa' => 7, 'rabu' => 7, 'kamis' => 7, 'jumat' => 0, 'sabtu' => 0, 'ahad' => 6];

            $this->post(route('wakasek.kaldik.level-structure', $calendar), ['structures' => $this->structures($jp)])
                ->assertSessionHas('success');

            $row = CalendarLevelStructure::where('academic_calendar_id', $calendar->id)->firstOrFail();
            $this->assertSame(6, $row->jpForDay('ahad'));
            $this->assertSame(0, $row->jpForDay('jumat'));
        } finally {
            $calendar->delete();
            $year->delete();
        }
    }

    public function test_ahad_active_with_jumat_active_is_allowed(): void
    {
        $schoolId = $this->schoolId();
        $suffix = now()->timestamp;
        $year = AcademicYear::create(['school_id' => $schoolId, 'name' => 'KaldikTA ' . $suffix, 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => false]);
        $calendar = AcademicCalendar::create([
            'school_id' => $schoolId,
            'academic_year_id' => $year->id,
            'name' => 'KaldikTest ' . $suffix,
            'semester' => 1,
            'source' => 'custom',
            'start_date' => '2026-07-13',
            'end_date' => '2026-12-19',
            'status' => 'draft',
            'version' => 1,
            'created_by' => User::where('email', 'wakakur@sit.sch.id')->firstOrFail()->id,
        ]);

        try {
            app(\App\Services\SchoolContext::class)->set(null);
            $this->actingAs(User::where('email', 'wakakur@sit.sch.id')->firstOrFail());

            $jp = ['senin' => 7, 'selasa' => 7, 'rabu' => 7, 'kamis' => 7, 'jumat' => 5, 'sabtu' => 0, 'ahad' => 2];

            $this->post(route('wakasek.kaldik.level-structure', $calendar), ['structures' => $this->structures($jp)])
                ->assertSessionHas('success');

            $row = CalendarLevelStructure::where('academic_calendar_id', $calendar->id)->firstOrFail();
            $this->assertSame(2, $row->jpForDay('ahad'));
        } finally {
            $calendar->delete();
            $year->delete();
        }
    }

    public function test_level_structure_normalizes_missing_days_and_rejects_invalid(): void
    {
        $schoolId = $this->schoolId();
        $suffix = now()->timestamp;
        $year = AcademicYear::create(['school_id' => $schoolId, 'name' => 'KaldikTA ' . $suffix, 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => false]);
        $calendar = AcademicCalendar::create([
            'school_id' => $schoolId,
            'academic_year_id' => $year->id,
            'name' => 'KaldikTest ' . $suffix,
            'semester' => 1,
            'source' => 'custom',
            'start_date' => '2026-07-13',
            'end_date' => '2026-12-19',
            'status' => 'draft',
            'version' => 1,
            'created_by' => User::where('email', 'wakakur@sit.sch.id')->firstOrFail()->id,
        ]);

        try {
            app(\App\Services\SchoolContext::class)->set(null);
            $this->actingAs(User::where('email', 'wakakur@sit.sch.id')->firstOrFail());

            $payload = ['structures' => [[
                'grade_level_start' => 1,
                'grade_level_end' => 6,
                'jp_duration_minutes' => 35,
                'jp_per_day' => ['senin' => 7, 'rabu' => 7],
            ]]];

            $this->post(route('wakasek.kaldik.level-structure', $calendar), $payload)
                ->assertSessionHas('success');

            $row = CalendarLevelStructure::where('academic_calendar_id', $calendar->id)->firstOrFail();
            $this->assertSame(7, $row->jpForDay('senin'));
            $this->assertSame(0, $row->jpForDay('selasa'));
            $this->assertSame(0, $row->jpForDay('ahad'));
            $this->assertSame(14, $row->jpPerWeek());

            $this->post(route('wakasek.kaldik.level-structure', $calendar), ['structures' => [[
                'grade_level_start' => 2,
                'grade_level_end' => 1,
                'jp_duration_minutes' => 35,
                'jp_per_day' => ['senin' => 7],
            ]]])->assertSessionHasErrors();
        } finally {
            $calendar->delete();
            $year->delete();
        }
    }

    public function test_level_holiday_override_and_submit_flow(): void
    {
        $schoolId = $this->schoolId();
        $suffix = now()->timestamp;
        $year = AcademicYear::create(['school_id' => $schoolId, 'name' => 'KaldikTA ' . $suffix, 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => false]);
        $calendar = AcademicCalendar::create([
            'school_id' => $schoolId,
            'academic_year_id' => $year->id,
            'name' => 'KaldikTest ' . $suffix,
            'semester' => 1,
            'source' => 'custom',
            'start_date' => '2026-07-13',
            'end_date' => '2026-12-19',
            'status' => 'draft',
            'version' => 1,
            'created_by' => User::where('email', 'wakakur@sit.sch.id')->firstOrFail()->id,
        ]);

        try {
            app(\App\Services\SchoolContext::class)->set(null);
            $this->actingAs(User::where('email', 'wakakur@sit.sch.id')->firstOrFail());

            $this->post(route('wakasek.kaldik.level-structure', $calendar), ['structures' => $this->structures(['senin' => 7, 'jumat' => 5])])
                ->assertSessionHas('success');
            $this->assertSame(1, CalendarLevelStructure::where('academic_calendar_id', $calendar->id)->count());

            $this->post(route('wakasek.kaldik.holiday.store', $calendar), ['date' => '2026-08-17', 'name' => 'HUT RI', 'type' => 'nasional'])->assertSessionHas('success');
            $this->assertSame(1, CalendarHoliday::where('academic_calendar_id', $calendar->id)->count());

            $this->post(route('wakasek.kaldik.override.store', $calendar), [
                'title' => 'PAS',
                'start_date' => '2026-12-07',
                'end_date' => '2026-12-18',
                'grade_level' => null,
                'notes' => null,
            ])->assertSessionHas('success');
            $this->assertSame(1, CalendarClassOverride::where('academic_calendar_id', $calendar->id)->count());
            $this->assertSame('other', CalendarClassOverride::where('academic_calendar_id', $calendar->id)->firstOrFail()->override_type);

            $this->post(route('wakasek.kaldik.submit', $calendar))->assertSessionHas('success');
            $this->assertSame('pending', $calendar->fresh()->status);
        } finally {
            $calendar->delete();
            $year->delete();
        }
    }

    public function test_batch_details_store_creates_multiple_holidays_and_overrides_without_submit(): void
    {
        $schoolId = $this->schoolId();
        $suffix = now()->timestamp;
        $year = AcademicYear::create(['school_id' => $schoolId, 'name' => 'KaldikRincian ' . $suffix, 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => false]);
        $calendar = AcademicCalendar::create([
            'school_id' => $schoolId,
            'academic_year_id' => $year->id,
            'name' => 'KaldikRincianTest ' . $suffix,
            'semester' => 1,
            'source' => 'custom',
            'start_date' => '2026-07-13',
            'end_date' => '2026-12-19',
            'status' => 'draft',
            'version' => 1,
            'created_by' => User::where('email', 'wakakur@sit.sch.id')->firstOrFail()->id,
        ]);

        try {
            app(\App\Services\SchoolContext::class)->set(null);
            $this->actingAs(User::where('email', 'wakakur@sit.sch.id')->firstOrFail());

            $this->post(route('wakasek.kaldik.details', $calendar), [
                'holidays' => [
                    ['date' => '2026-08-17', 'name' => 'HUT RI', 'type' => 'nasional'],
                    ['date' => '2026-12-25', 'name' => 'Natal', 'type' => 'rutin'],
                ],
                'overrides' => [
                    ['title' => 'PAS', 'start_date' => '2026-12-07', 'end_date' => '2026-12-18', 'grade_level' => null, 'notes' => null],
                    ['title' => 'RAPAT', 'start_date' => '2026-12-19', 'end_date' => '2026-12-19', 'grade_level' => 6, 'notes' => 'rapat akhir tahun'],
                ],
            ])->assertSessionHas('success');

            $this->assertSame(2, CalendarHoliday::where('academic_calendar_id', $calendar->id)->count());
            $this->assertSame(2, CalendarClassOverride::where('academic_calendar_id', $calendar->id)->count());
            $this->assertSame(2, CalendarClassOverride::where('academic_calendar_id', $calendar->id)->where('override_type', 'other')->count());
            $this->assertTrue($calendar->fresh()->isDraft());

            $this->post(route('wakasek.kaldik.details', $calendar), [
                'holidays' => [
                    ['date' => '', 'name' => '', 'type' => 'nasional'],
                ],
            ])->assertSessionHasErrors();
            $this->assertSame(2, CalendarHoliday::where('academic_calendar_id', $calendar->id)->count());
        } finally {
            $calendar->delete();
            $year->delete();
        }
    }
}