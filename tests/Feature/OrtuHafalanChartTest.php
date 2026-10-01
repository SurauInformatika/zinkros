<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use Tests\TestCase;

class OrtuHafalanChartTest extends TestCase
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

    public function test_hafalan_page_renders_with_chart(): void
    {
        $ortu = User::where('email', 'ibu.demo@mail.com')->firstOrFail();
        $this->actingAs($ortu);

        $child = $ortu->students()->firstOrFail();
        session(['active_child_id' => $child->id]);

        $resp = $this->get(route('ortu.hafalan'));
        $resp->assertOk();
        $resp->assertSee('Grafik Hafalan', false);
        $resp->assertSee('/ortu/hafalan/chart', false);
    }

    public function test_hafalan_chart_endpoint_for_active_child(): void
    {
        $ortu = User::where('email', 'ibu.demo@mail.com')->firstOrFail();
        $this->actingAs($ortu);

        $child = $ortu->students()->firstOrFail();
        session(['active_child_id' => $child->id]);

        $resp = $this->getJson(route('ortu.hafalan.chart') . '?filter=ta_init');
        $resp->assertOk();
        $data = $resp->json();
        $this->assertArrayHasKey('labels', $data);
        $this->assertArrayHasKey('ayat_ziadah', $data);
        $this->assertArrayHasKey('ayat_murajaah', $data);
        $this->assertArrayHasKey('stats', $data);
        $this->assertArrayHasKey('total', $data['stats']);
    }

    public function test_hafalan_chart_scoped_to_ortu_children(): void
    {
        $ortu = User::where('email', 'ibu.demo@mail.com')->firstOrFail();
        $this->actingAs($ortu);

        $ortuIds = $ortu->students()->pluck('students.id')->all();

        // a student NOT belonging to this parent within same school
        $foreign = Student::where('school_id', $ortu->school_id)
            ->whereNotIn('id', $ortuIds)
            ->first();

        if ($foreign) {
            session(['active_child_id' => $foreign->id]);
            $resp = $this->getJson(route('ortu.hafalan.chart') . '?filter=ta_init');
            $resp->assertStatus(403);
        } else {
            $this->assertTrue(true, 'No foreign student found to test against');
        }
    }
}
