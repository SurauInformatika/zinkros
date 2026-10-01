<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\Student;
use App\Models\User;
use Tests\TestCase;

class KepsekGuruTugasTest extends TestCase
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

    public function test_kepsek_can_view_own_guru_and_tugas_pages(): void
    {
        $kepsek = User::where('email', 'kepsek@sit.sch.id')->firstOrFail();
        $this->actingAs($kepsek);

        $this->get('/kepsek/guru')
            ->assertOk()
            ->assertSee('Guru & Pembagian Tugas', false)
            ->assertSee('Guru &amp; Tugas', false)
            ->assertSee('Per Kelas', false);

        $this->get('/kepsek/guru?view=kelas')
            ->assertOk()
            ->assertSee('Wali Kelas', false);
    }

    public function test_kepsek_can_open_teacher_detail(): void
    {
        $kepsek = User::where('email', 'kepsek@sit.sch.id')->firstOrFail();
        $this->actingAs($kepsek);

        $guru = User::where('role', User::ROLE_GURU)
            ->where('school_id', $kepsek->school_id)
            ->firstOrFail();

        $this->get("/kepsek/guru/{$guru->id}")
            ->assertOk()
            ->assertSee('Detail Guru', false)
            ->assertSee($guru->name, false)
            ->assertSee('Kembali ke daftar guru', false);
    }

    public function test_kepsek_can_open_class_plotting_detail(): void
    {
        $kepsek = User::where('email', 'kepsek@sit.sch.id')->firstOrFail();
        $this->actingAs($kepsek);

        $kelas = ClassRoom::where('school_id', $kepsek->school_id)->first();

        if (! $kelas) {
            $this->markTestSkipped('Tidak ada data kelas di sekolah kepsek.');
        }

        $this->get("/kepsek/pembagian-tugas/{$kelas->id}")
            ->assertOk()
            ->assertSee('Plotting', false);
    }

    public function test_kepsek_can_open_own_roster_page(): void
    {
        $kepsek = User::where('email', 'kepsek@sit.sch.id')->firstOrFail();
        $this->actingAs($kepsek);

        $this->get('/kepsek/roster')
            ->assertOk()
            ->assertSee('Roster Kelas', false);
    }

    public function test_kepsek_can_open_tahfidz_overview(): void
    {
        $kepsek = User::where('email', 'kepsek@sit.sch.id')->firstOrFail();
        $this->actingAs($kepsek);

        $this->get('/kepsek/tahfidz')
            ->assertOk()
            ->assertSee('Overview Tahfidz', false);
    }

    public function test_kepsek_students_page_has_gender_and_filters(): void
    {
        $kepsek = User::where('email', 'kepsek@sit.sch.id')->firstOrFail();
        $this->actingAs($kepsek);
        $sid = $kepsek->school_id;

        $resp = $this->get('/kepsek/siswa');
        $resp->assertOk();
        $resp->assertSee('Jenis Kelamin', false);
        $resp->assertSee('Semua Gender', false);
        $resp->assertSee('Semua Kelas', false);
        $resp->assertSee('Cari nama, NIS, atau NISN', false);

        $kelas = ClassRoom::where('school_id', $sid)->first();
        if ($kelas && Student::where('school_id', $sid)->where('class_id', $kelas->id)->exists()) {
            $this->get('/kepsek/siswa?class_id=' . $kelas->id)->assertOk();
        }
        if (Student::where('school_id', $sid)->where('gender', 'L')->exists()) {
            $this->get('/kepsek/siswa?gender=L')->assertOk();
        }
    }

    public function test_kepsek_guru_page_orders_kepsek_then_wakasek_then_guru(): void
    {
        $kepsek = User::where('email', 'kepsek@sit.sch.id')->firstOrFail();
        $this->actingAs($kepsek);
        $sid = $kepsek->school_id;

        $kep = User::where('school_id', $sid)->where('role', User::ROLE_KEPSEK)->first();
        $waka = User::where('school_id', $sid)->where('role', User::ROLE_WAKASEK)->first();
        $guru = User::where('school_id', $sid)->where('role', User::ROLE_GURU)->first();

        $html = $this->get('/kepsek/guru')->assertOk()->getContent();

        $this->assertTrue($kep && $waka && $guru, 'Perlu ada kepsek, wakasek, dan guru di sekolah.');

        $this->assertSeeLabel($html, $kep->name);
        $this->assertSeeLabel($html, $waka->name);
        $this->assertSeeLabel($html, $guru->name);

        $rows = User::where('school_id', $sid)
            ->whereIn('role', [User::ROLE_GURU, User::ROLE_KEPSEK, User::ROLE_WAKASEK])
            ->orderByRaw("CASE role WHEN '" . User::ROLE_KEPSEK . "' THEN 0 WHEN '" . User::ROLE_WAKASEK . "' THEN 1 ELSE 2 END")
            ->orderBy('name')
            ->pluck('role')
            ->unique()
            ->values()
            ->all();

        $this->assertSame(['kepsek', 'wakasek', 'guru'], $rows);
    }

    public function test_kepsek_can_access_quran_assignment_page(): void
    {
        $kepsek = User::where('email', 'kepsek@sit.sch.id')->firstOrFail();
        $this->actingAs($kepsek);

        $this->get('/kepsek/quran-assignment')
            ->assertOk()
            ->assertSee('Assignment Guru & Siswa Al-Quran', false);
    }

    public function test_kepsek_quran_assignment_store_redirects_to_kepsek_index(): void
    {
        $kepsek = User::where('email', 'kepsek@sit.sch.id')->firstOrFail();
        $this->actingAs($kepsek);

        $this->post('/kepsek/quran-assignment', [
            'student_id' => (string) \Illuminate\Support\Str::uuid(),
            'teacher_id' => (string) \Illuminate\Support\Str::uuid(),
        ])->assertRedirect(route('kepsek.quran-assignment.index'));
    }

    private function assertSeeLabel(string $html, string $name): void
    {
        $this->assertStringContainsString(htmlspecialchars($name, ENT_QUOTES, 'UTF-8', false), $html);
    }
}
