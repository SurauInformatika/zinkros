<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class MuridRoleTest extends TestCase
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

    public function test_murid_dashboard_renders(): void
    {
        $murid = User::where('email', 'murid@sit.sch.id')->firstOrFail();
        $this->actingAs($murid);

        $this->get('/murid/dashboard')->assertOk()->assertSee('Dashboard', false);
    }

    public function test_murid_cannot_access_admin_pages(): void
    {
        $murid = User::where('email', 'murid@sit.sch.id')->firstOrFail();
        $this->actingAs($murid);

        $this->get('/admin/dashboard')->assertForbidden();
    }

    public function test_murid_role_removed_from_pengguna_menu(): void
    {
        $this->assertNotContains('murid', \App\Models\School::PENGGUNA_ROLES);

        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $this->actingAs($admin);

        $this->get('/admin/pengguna')->assertDontSee('role=murid', false);
    }
}