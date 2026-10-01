<?php

namespace Tests\Feature;

use App\Models\AcademicCalendar;
use App\Models\AcademicYear;
use App\Models\CalendarLevelStructure;
use App\Models\User;
use Tests\TestCase;

class WakakurKaldikMenitSummaryTest extends TestCase
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
        $suffix = now()->timestamp . rand(100, 999);
        $year = AcademicYear::create(['school_id' => $this->schoolId(), 'name' => 'TA ' . $suffix, 'start_date' => '2026-06-01', 'end_date' => '2027-06-30', 'is_active' => false]);
        $cal = AcademicCalendar::create(array_merge([
            'school_id' => $this->schoolId(),
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

        return [$cal, $year];
    }

    private function seedLevel(AcademicCalendar $cal, int $start, int $end, int $duration, array $jpPerDay): void
    {
        CalendarLevelStructure::create([
            'academic_calendar_id' => $cal->id,
            'grade_level_start' => $start,
            'grade_level_end' => $end,
            'jp_duration_minutes' => $duration,
            'jp_per_day' => $jpPerDay,
        ]);
    }

    public function test_minutes_by_level_uses_per_level_duration_and_jp(): void
    {
        [$cal, $year] = $this->makeCalendar();
        try {
            $this->seedLevel($cal, 1, 3, 35, ['senin' => 7, 'selasa' => 7, 'rabu' => 7, 'kamis' => 7, 'jumat' => 5, 'sabtu' => 0, 'ahad' => 0]);
            $this->seedLevel($cal, 4, 6, 40, ['senin' => 6, 'selasa' => 6, 'rabu' => 6, 'kamis' => 6, 'jumat' => 4, 'sabtu' => 0, 'ahad' => 0]);

            $cal->load('levelStructures');
            $rows = $cal->minutesByLevel();
            $eff = $cal->effectiveWeeks();

            $this->assertCount(2, $rows);
            $this->assertSame('Kelas 1 – 3', $rows[0]['label']);
            $this->assertSame(35, $rows[0]['duration']);
            $this->assertSame(33, $rows[0]['jp_per_week']);
            $this->assertSame(1155, $rows[0]['minutes_per_week']);
            $this->assertSame(1155 * $eff, $rows[0]['minutes_year']);

            $this->assertSame('Kelas 4 – 6', $rows[1]['label']);
            $this->assertSame(40, $rows[1]['duration']);
            $this->assertSame(28, $rows[1]['jp_per_week']);
            $this->assertSame(1120, $rows[1]['minutes_per_week']);
        } finally {
            $cal->delete();
            $year->delete();
        }
    }

    public function test_minutes_by_level_empty_when_no_levels(): void
    {
        [$cal, $year] = $this->makeCalendar();
        try {
            $cal->load('levelStructures');
            $this->assertSame([], $cal->minutesByLevel());
            $this->assertSame(0, $cal->jpPerWeek());
        } finally {
            $cal->delete();
            $year->delete();
        }
    }

    public function test_edit_page_renders_merged_structure_card(): void
    {
        [$cal, $year] = $this->makeCalendar();
        try {
            $this->seedLevel($cal, 1, 3, 35, ['senin' => 7, 'selasa' => 7, 'rabu' => 7, 'kamis' => 7, 'jumat' => 5, 'sabtu' => 0, 'ahad' => 0]);

            app(\App\Services\SchoolContext::class)->set(null);
            $this->actingAs(User::where('email', 'wakakur@sit.sch.id')->firstOrFail());

            $resp = $this->get(route('wakasek.kaldik.edit', $cal));
            $resp->assertOk();
            $resp->assertSee('Struktur &amp; Durasi JP per Jenjang', false);
            $resp->assertSee('1155', false);
            $resp->assertSee('JP/mgg', false);
            $resp->assertSee('structures[0][jp_per_day][senin]', false);
            $resp->assertSee('structures[0][jp_duration_minutes]', false);
            $resp->assertSee('value="35"', false);
        } finally {
            $cal->delete();
            $year->delete();
        }
    }

    public function test_edit_page_disables_inputs_on_final(): void
    {
        [$cal, $year] = $this->makeCalendar(['status' => 'final']);
        try {
            $this->seedLevel($cal, 1, 6, 35, ['senin' => 7, 'selasa' => 7, 'rabu' => 7, 'kamis' => 7, 'jumat' => 5, 'sabtu' => 0, 'ahad' => 0]);

            app(\App\Services\SchoolContext::class)->set(null);
            $this->actingAs(User::where('email', 'wakakur@sit.sch.id')->firstOrFail());

            $resp = $this->get(route('wakasek.kaldik.edit', $cal));
            $resp->assertOk();
            $resp->assertSee('structures[0][jp_duration_minutes]', false);
            $resp->assertSee('disabled', false);
            $resp->assertDontSee('Tambah Jenjang');
            $resp->assertDontSee('Simpan Struktur');
        } finally {
            $cal->delete();
            $year->delete();
        }
    }

    public function test_level_structure_gap_triggers_soft_warning(): void
    {
        [$cal, $year] = $this->makeCalendar();
        try {
            app(\App\Services\SchoolContext::class)->set(null);
            $this->actingAs(User::where('email', 'wakakur@sit.sch.id')->firstOrFail());

            $this->post(route('wakasek.kaldik.level-structure', $cal), ['structures' => [[
                'grade_level_start' => 7,
                'grade_level_end' => 9,
                'jp_duration_minutes' => 35,
                'jp_per_day' => ['senin' => 7, 'jumat' => 5],
            ]]])
                ->assertSessionHas('success')
                ->assertSessionHas('warning');

            $this->assertSame(1, CalendarLevelStructure::where('academic_calendar_id', $cal->id)->count());
        } finally {
            $cal->delete();
            $year->delete();
        }
    }

    public function test_level_structure_requires_jp_per_day(): void
    {
        [$cal, $year] = $this->makeCalendar();
        try {
            app(\App\Services\SchoolContext::class)->set(null);
            $this->actingAs(User::where('email', 'wakakur@sit.sch.id')->firstOrFail());

            $this->post(route('wakasek.kaldik.level-structure', $cal), ['structures' => [[
                'grade_level_start' => 7,
                'grade_level_end' => 9,
                'jp_duration_minutes' => 35,
            ]]])->assertSessionHasErrors('structures.0.jp_per_day');
        } finally {
            $cal->delete();
            $year->delete();
        }
    }
}