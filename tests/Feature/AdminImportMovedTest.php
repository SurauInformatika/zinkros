<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AdminImportMovedTest extends TestCase
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

    private function actingAsAdmin(): void
    {
        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $this->actingAs($admin);
    }

    public function test_sidebar_removed_import_excel_link(): void
    {
        $this->actingAsAdmin();

        $resp = $this->get(route('admin.dashboard'));
        $resp->assertOk();
        $resp->assertDontSee('Import Excel', false);
        $resp->assertDontSee('/admin/excel"', false);
    }

    public function test_guru_page_renders_import_form(): void
    {
        $this->actingAsAdmin();

        $resp = $this->get(route('admin.guru.index'));
        $resp->assertOk();
        $resp->assertSee('Import Guru', false);
        $resp->assertSee('Download Template', false);
        $resp->assertSee('/admin/excel/import/guru', false);
    }

    public function test_staff_page_renders_import_form(): void
    {
        $this->actingAsAdmin();

        $resp = $this->get(route('admin.staff.index'));
        $resp->assertOk();
        $resp->assertSee('Import Staff', false);
        $resp->assertSee('/admin/excel/import/staff', false);
    }

    public function test_kelas_page_has_no_import_form(): void
    {
        $this->actingAsAdmin();

        $this->get('/admin/excel/import/kelas')->assertNotFound();
        $this->get('/admin/excel/template/kelas')->assertNotFound();

        $resp = $this->get(route('admin.kelas.index'));
        $resp->assertOk();
        $resp->assertDontSee('Import Kelas', false);
        $resp->assertDontSee('/admin/excel/import/kelas', false);
    }

    public function test_subject_page_has_no_import_form(): void
    {
        $this->actingAsAdmin();

        $this->get('/admin/excel/import/subject')->assertNotFound();
        $this->get('/admin/excel/template/subject')->assertNotFound();

        $resp = $this->get(route('admin.subject.index'));
        $resp->assertOk();
        $resp->assertDontSee('Import Mata Pelajaran', false);
        $resp->assertDontSee('/admin/excel/import/subject', false);
    }

    public function test_siswa_page_renders_import_form(): void
    {
        $this->actingAsAdmin();

        $resp = $this->get(route('admin.siswa.index'));
        $resp->assertOk();
        $resp->assertSee('Import Siswa', false);
        $resp->assertSee('Preview Import Siswa', false);
        $resp->assertSee('/admin/siswa/import/preview', false);
    }

    public function test_old_excel_index_route_is_gone(): void
    {
        $this->actingAsAdmin();

        $this->get('/admin/excel')->assertNotFound();
    }
}