<?php

namespace Tests\Feature;

use App\Models\AttendanceClass;
use App\Models\User;
use Tests\TestCase;

class OrtuAbsensiTabTest extends TestCase
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

    private function actingAsOrtu()
    {
        $ortu = User::where('email', 'ibu.demo@mail.com')->firstOrFail();
        $this->actingAs($ortu);
        $child = $ortu->students()->firstOrFail();
        session(['active_child_id' => $child->id]);
    }

    public function test_absensi_index_renders_two_links(): void
    {
        $this->actingAsOrtu();

        $resp = $this->get(route('ortu.absensi'));
        $resp->assertOk();
        $resp->assertSee('Absensi Kelas', false);
        $resp->assertSee('Absensi Mapel', false);
        $resp->assertSee('Kehadiran harian siswa di kelas', false);
        $resp->assertSee('Kehadiran siswa per mata pelajaran', false);
    }

    public function test_absensi_kelas_page_renders_period_filter(): void
    {
        $this->actingAsOrtu();

        $resp = $this->get(route('ortu.absensi.kelas'));
        $resp->assertOk();
        $resp->assertSee('Absensi Kelas', false);
        $resp->assertSee('Pekan Ini', false);
        $resp->assertSee('Bulan Ini', false);
        $resp->assertSee('Semester Ini', false);
        $resp->assertSee('Tahun Ajaran Ini', false);
        $resp->assertSee('Periode:', false);
    }

    public function test_absensi_mapel_page_renders_period_filter(): void
    {
        $this->actingAsOrtu();

        $resp = $this->get(route('ortu.absensi.mapel'));
        $resp->assertOk();
        $resp->assertSee('Absensi Mapel', false);
        $resp->assertSee('Pekan Ini', false);
        $resp->assertSee('Tahun Ajaran Ini', false);
        $resp->assertSee('Periode:', false);
    }

    public function test_ortu_layout_renders_mobile_bottom_nav(): void
    {
        $this->actingAsOrtu();

        $resp = $this->get(route('ortu.absensi'));
        $resp->assertOk();
        $resp->assertSee('fixed bottom-0', false);
        $resp->assertSee('lg:hidden', false);
        $resp->assertSee('Beranda', false);
        $resp->assertSee('Hafalan', false);
        $resp->assertSee('/ortu/dashboard', false);
    }

    public function test_absensi_mapel_shows_records_when_child_has_data(): void
    {
        $ortu = User::where('email', 'ibu.demo@mail.com')->firstOrFail();
        $this->actingAs($ortu);

        // Ghaziyah has attendance subject records this month (Aug 2026)
        $ghaziyah = $ortu->students()->where('name', 'like', '%Ghaziyah%')->first();
        if (! $ghaziyah) {
            $this->markTestSkipped('No child with attendance data found.');
        }

        session(['active_child_id' => $ghaziyah->id]);

        $resp = $this->get(route('ortu.absensi.mapel') . '?period=bulan');
        $resp->assertOk();
        $resp->assertSee('Mata Pelajaran', false);
        $resp->assertSee('HADIR', false);
        $resp->assertSee('Periode:', false);
    }

    public function test_absensi_kelas_hides_recorder_column_on_mobile_when_data_exists(): void
    {
        $ortu = User::where('email', 'ibu.demo@mail.com')->firstOrFail();
        $this->actingAs($ortu);

        $child = $ortu->students()->firstOrFail();
        session(['active_child_id' => $child->id]);

        $record = AttendanceClass::create([
            'school_id' => $child->school_id,
            'class_id' => $child->class_id,
            'student_id' => $child->id,
            'recorded_by' => $ortu->id,
            'status' => AttendanceClass::STATUS_HADIR,
            'date' => now()->toDateString(),
            'notes' => 'Test recorder column',
        ]);

        try {
            $resp = $this->get(route('ortu.absensi.kelas') . '?period=bulan');
            $resp->assertOk();
            $resp->assertSee('Dicatat Oleh', false);
            // The recorder column must be hidden on mobile (only shown sm and up)
            $resp->assertSee('hidden sm:table-cell', false);
        } finally {
            $record->delete();
        }
    }
}
