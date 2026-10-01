<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class WakakurKelasSiswaTest extends TestCase
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

    public function test_kelas_page_renders_both_tabs(): void
    {
        $this->actingAs(User::where('email', 'wakakur@dafi.com')->firstOrFail());

        $this->get(route('wakasek.base.classes', ['tab' => 'kelas']))
            ->assertOk()
            ->assertSee('Kelas', false)
            ->assertSee('Siswa', false);

        $this->get(route('wakasek.base.classes', ['tab' => 'siswa']))
            ->assertOk()
            ->assertSee('Cari nama, NIS, atau NISN', false)
            ->assertSee('Semua Kelas', false)
            ->assertSee('Semua Gender', false)
            ->assertSee('Laki-laki', false)
            ->assertSee('Perempuan', false);
    }

    public function test_siswa_tab_filters_by_gender(): void
    {
        $this->actingAs(User::where('email', 'wakakur@dafi.com')->firstOrFail());

        $this->get(route('wakasek.base.classes', ['tab' => 'siswa', 'gender' => 'L']))
            ->assertOk()
            ->assertSee('Laki-laki', false);
    }

    public function test_siswa_tab_filters_by_search(): void
    {
        $this->actingAs(User::where('email', 'wakakur@dafi.com')->firstOrFail());

        $this->get(route('wakasek.base.classes', ['tab' => 'siswa', 'search' => 'zzz-non-existent']))
            ->assertOk()
            ->assertSee('Tidak ada siswa yang cocok', false);
    }
}
