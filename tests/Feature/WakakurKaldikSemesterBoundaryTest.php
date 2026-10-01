<?php

namespace Tests\Feature;

use App\Models\AcademicCalendar;
use App\Models\AcademicYear;
use App\Models\CalendarLevelStructure;
use App\Models\User;
use App\Support\KaldikDocument;
use Tests\TestCase;

class WakakurKaldikSemesterBoundaryTest extends TestCase
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

    private function makeCalendar(array $extra = []): array
    {
        $schoolId = $this->schoolId();
        $suffix = now()->timestamp . rand(100, 999);
        $year = AcademicYear::create(['school_id' => $schoolId, 'name' => 'TA ' . $suffix, 'start_date' => '2026-06-01', 'end_date' => '2027-06-30', 'is_active' => false]);
        $cal = AcademicCalendar::create(array_merge([
            'school_id' => $schoolId,
            'academic_year_id' => $year->id,
            'name' => 'Bdr ' . $suffix,
            'semester' => 0,
            'source' => 'custom',
            'start_date' => '2026-07-13',
            'end_date' => '2027-06-30',
            'status' => 'draft',
            'version' => 1,
            'created_by' => User::where('email', 'wakakur@sit.sch.id')->firstOrFail()->id,
        ], $extra));

        CalendarLevelStructure::create(['academic_calendar_id' => $cal->id, 'grade_level_start' => 1, 'grade_level_end' => 12, 'jp_duration_minutes' => 35, 'jp_per_day' => ['senin' => 7]]);

        return [$cal, $year];
    }

    public function test_semester_blocks_respect_explicit_semester_2_date(): void
    {
        [$cal, $year] = $this->makeCalendar(['semester_2_start_date' => '2026-11-30']);
        try {
            $doc = new KaldikDocument($cal);
            $labels = array_map(fn ($b) => $b['label'], $doc->semesterBlocks);
            $counts = array_map(fn ($b) => count($b['months']), $doc->semesterBlocks);
            $this->assertSame(['Semester Ganjil', 'Semester Genap'], $labels);
            $this->assertSame([5, 7], $counts);
        } finally {
            $cal->delete();
            $year->delete();
        }
    }

    public function test_semester_blocks_fall_back_to_month_heuristic_without_date(): void
    {
        [$cal, $year] = $this->makeCalendar();
        try {
            $doc = new KaldikDocument($cal);
            $counts = array_map(fn ($b) => count($b['months']), $doc->semesterBlocks);
            $this->assertSame([6, 6], $counts);
        } finally {
            $cal->delete();
            $year->delete();
        }
    }

    public function test_store_persists_semester_2_start_date(): void
    {
        $schoolId = $this->schoolId();
        $suffix = now()->timestamp . rand(100, 999);
        $year = AcademicYear::create(['school_id' => $schoolId, 'name' => 'TS ' . $suffix, 'start_date' => '2026-06-01', 'end_date' => '2027-06-30', 'is_active' => false]);
        $cal = null;

        try {
            app(\App\Services\SchoolContext::class)->set(null);
            $this->actingAs(User::where('email', 'wakakur@sit.sch.id')->firstOrFail());

            $this->post(route('wakasek.kaldik.store'), [
                'academic_year_id' => $year->id,
                'name' => 'Bdr2 ' . $suffix,
                'source' => 'custom',
                'start_date' => '2026-07-13',
                'end_date' => '2027-06-30',
                'semester_2_start_date' => '2027-01-04',
            ])->assertRedirect();

            $cal = AcademicCalendar::where('school_id', $schoolId)->where('academic_year_id', $year->id)->firstOrFail();
            $this->assertSame('2027-01-04', $cal->semester_2_start_date->format('Y-m-d'));
            $this->assertTrue($cal->hasExplicitSemesterBoundaries());
        } finally {
            $cal?->delete();
            $year->delete();
        }
    }

    public function test_store_rejects_semester_2_start_date_before_start(): void
    {
        app(\App\Services\SchoolContext::class)->set(null);
        $this->actingAs(User::where('email', 'wakakur@sit.sch.id')->firstOrFail());

        $this->post(route('wakasek.kaldik.store'), [
            'academic_year_id' => AcademicYear::first()->id,
            'name' => 'Reject',
            'source' => 'custom',
            'start_date' => '2026-07-13',
            'end_date' => '2027-06-30',
            'semester_2_start_date' => '2026-05-01',
        ])->assertSessionHasErrors('semester_2_start_date');
    }
}