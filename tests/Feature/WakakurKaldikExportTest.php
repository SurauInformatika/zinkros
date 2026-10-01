<?php

namespace Tests\Feature;

use App\Models\AcademicCalendar;
use App\Models\User;
use Tests\TestCase;

class WakakurKaldikExportTest extends TestCase
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

    private function calendar(): AcademicCalendar
    {
        return AcademicCalendar::where('school_id', '01a01792-3680-7215-ba72-9b545e824c1d')
            ->where('status', 'final')
            ->latest('version')
            ->firstOrFail();
    }

    public function test_wakakur_can_export_school_calendar_pdf(): void
    {
        app(\App\Services\SchoolContext::class)->set(null);
        $this->actingAs(User::where('email', 'wakakur@dafi.com')->firstOrFail());

        $res = $this->get(route('wakasek.kaldik.export', $this->calendar()));
        $res->assertOk();
        $res->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('inline; filename=', (string) $res->headers->get('Content-Disposition'));
    }

    public function test_kepsek_can_export_school_calendar_pdf(): void
    {
        app(\App\Services\SchoolContext::class)->set(null);
        $this->actingAs(User::where('email', 'kepsek@dafi.com')->firstOrFail());

        $res = $this->get(route('kepsek.kaldik.export', $this->calendar()));
        $res->assertOk();
        $res->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('inline; filename=', (string) $res->headers->get('Content-Disposition'));
    }

    public function test_export_is_forbidden_for_other_school(): void
    {
        app(\App\Services\SchoolContext::class)->set(null);
        $this->actingAs(User::where('email', 'wakakur@sit.sch.id')->firstOrFail());

        $this->get(route('wakasek.kaldik.export', $this->calendar()))
            ->assertForbidden();
    }

    public function test_export_pdf_contains_structure_content(): void
    {
        $cal = $this->calendar();
        $this->actingAs(User::where('email', 'wakakur@dafi.com')->firstOrFail());

        $cal->load(['school', 'academicYear', 'template', 'creator', 'approver', 'levelStructures', 'holidays', 'classOverrides']);

        $html = view('kepsek.kaldik-pdf', ['kaldik' => $cal])->render();
        $this->assertStringContainsString($cal->school->name, $html);
        $this->assertStringNotContainsString('Kalender Pembelajaran per Bulan', $html);
        $this->assertStringContainsString('Semester Ganjil', $html);
        $this->assertStringNotContainsString('Struktur &amp; Durasi Jam Pelajaran per Jenjang', $html);
        $this->assertStringNotContainsString('Alokasi Waktu Pembelajaran', $html);
        $this->assertStringNotContainsString('Rincian Hari Libur', $html);
        $this->assertStringNotContainsString('Agenda / Kegiatan Sekolah', $html);
        $this->assertStringNotContainsString('Total Minggu', $html);
        $this->assertStringNotContainsString('Total JP', $html);
        $this->assertMatchesRegularExpression('/class="cell c-(red|green|blue|dim)"/', $html);
        $this->assertMatchesRegularExpression('/<div class="notes-head">Keterangan<\/div>/', $html);
    }

    public function test_export_pdf_marks_weekly_holiday_and_learning_days(): void
    {
        $cal = $this->calendar();
        $doc = new \App\Support\KaldikDocument($cal);
        $month = $doc->monthCells[0];

        $this->assertNotEmpty($month['rows']);

        $flattened = [];
        foreach ($month['rows'] as $row) {
            foreach ($row as $cell) {
                if ($cell !== null) {
                    $flattened[] = $cell;
                }
            }
        }

        $this->assertNotEmpty($flattened);
        $tags = array_merge(...array_map(fn ($c) => $c['tags'], $flattened));
        $this->assertContains('Efektif', array_column($tags, 'text'));
        $this->assertContains('Libur', array_column($tags, 'text'));
    }
}