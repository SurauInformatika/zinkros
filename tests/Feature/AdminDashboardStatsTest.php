<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AdminDashboardStatsTest extends TestCase
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

    public function test_admin_dashboard_renders_second_row_stats(): void
    {
        $admin = User::where('email', 'admin@sit.sch.id')->firstOrFail();
        $this->actingAs($admin);

        $resp = $this->get(route('admin.dashboard'));
        $resp->assertOk();

        $resp->assertSee('Kehadiran Pekan Ini', false);
        $resp->assertSee('Total Setoran Tahfidz', false);
        $resp->assertSee('Rata-rata Skor Tahfidz', false);
        $resp->assertSee('Rata-rata Nilai', false);
    }
}