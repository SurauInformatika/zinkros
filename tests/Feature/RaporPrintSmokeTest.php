<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class RaporPrintSmokeTest extends TestCase
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

    public function test_student_print_renders_default_format(): void
    {
        $user = User::where('email', 'surau.informatika@gmail.com')->firstOrFail();

        $student = \App\Models\Student::where('class_id', function ($q) use ($user) {
            $q->select('class_id')->from('class_homerooms')->where('user_id', $user->id)->first();
        })->firstOrFail();

        $resp = $this->actingAs($user)->get(route('guru.rapor.student', $student->id));
        $resp->assertOk();
        $resp->assertSee('LAPORAN HASIL BELAJAR PESERTA DIDIK');
        $resp->assertSee('Nilai Pengetahuan dan Keterampilan');
        $resp->assertSee('Mata Pelajaran');
        $resp->assertSee('Nilai Akhir');
        $resp->assertSee('Predikat');
        $resp->assertSee('Orang Tua');
        $resp->assertSee('Kepala Sekolah');
    }

    public function test_student_print_works_with_8d_no_data(): void
    {
        $user = User::where('email', 'suhe@mail.com')->firstOrFail();

        $student = \App\Models\Student::where('class_id', function ($q) use ($user) {
            $q->select('class_id')->from('class_homerooms')->where('user_id', $user->id)->first();
        })->firstOrFail();

        $this->actingAs($user)->get(route('guru.rapor.student', $student->id))->assertOk();
    }
}